<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dispositions extends Admin_Controller
{
	const PER_PAGE = 15;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Disposition_model', 'Incoming_letter_model', 'User_model'));
		$this->load->library('pagination');
	}

	/** Daftar semua lembar disposisi (untuk Kepala Sekolah, TU, Superuser). */
	public function index()
	{
		$this->require_role(array('kepala_sekolah', 'tu', 'superuser', 'admin'));

		$filters = array(
			'q'        => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
			'status'   => (string) $this->input->get('status', TRUE),
			'priority' => (string) $this->input->get('priority', TRUE),
			'overdue'  => (string) $this->input->get('overdue', TRUE),
		);

		$total  = $this->Disposition_model->count_search($filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('disposisi', $total, self::PER_PAGE));

		$this->render('dispositions/index', array(
			'page_title'   => 'Disposisi Surat',
			'dispositions' => $this->Disposition_model->search($filters, self::PER_PAGE, $offset),
			'total'        => $total,
			'filters'      => $filters,
			'offset'       => $offset,
			'pagination'   => $this->pagination->create_links(),
		));
	}

	/** Disposisi Saya (khusus Pegawai penerima tugas). */
	public function my_dispositions()
	{
		$user_id = (int) $this->current_user['id'];
		$filters = array(
			'q'        => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
			'status'   => (string) $this->input->get('status', TRUE),
			'priority' => (string) $this->input->get('priority', TRUE),
		);

		$total  = $this->Disposition_model->count_my_dispositions($user_id, $filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('disposisi-saya', $total, self::PER_PAGE));

		$this->render('dispositions/my_dispositions', array(
			'page_title'   => 'Disposisi Saya',
			'dispositions' => $this->Disposition_model->my_dispositions($user_id, $filters, self::PER_PAGE, $offset),
			'total'        => $total,
			'filters'      => $filters,
			'offset'       => $offset,
			'pagination'   => $this->pagination->create_links(),
		));
	}

	/** Form pembuatan disposisi baru (Kepala Sekolah). */
	public function create($incoming_letter_id = 0)
	{
		$this->require_role(array('kepala_sekolah', 'superuser', 'admin'));

		$incoming = NULL;
		if ($incoming_letter_id)
		{
			$incoming = $this->Incoming_letter_model->find((int) $incoming_letter_id);
			if ( ! $incoming)
			{
				$this->not_found('Surat masuk rujukan tidak ditemukan.');
			}
		}

		$this->render('dispositions/create', array(
			'page_title'         => 'Buat Lembar Disposisi',
			'incoming'           => $incoming,
			'disposition_number' => $this->Disposition_model->generate_disposition_number(),
			'employees'          => $this->User_model->get_active_employees(),
		));
	}

	/** Simpan disposisi dan sebarkan ke pegawai penerima. */
	public function store()
	{
		$this->require_role(array('kepala_sekolah', 'superuser', 'admin'));
		$this->require_post();

		$this->load->library('form_validation');
		$fv = $this->form_validation;
		$fv->set_rules('disposition_number', 'Nomor Disposisi', 'trim|required|max_length[50]');
		$fv->set_rules('disposition_date', 'Tanggal Disposisi', 'required');
		$fv->set_rules('priority', 'Sifat / Prioritas', 'required');
		$fv->set_rules('instruction', 'Instruksi Tindak Lanjut', 'trim|required');

		$recipients = $this->input->post('recipient_ids');
		if (empty($recipients) || ! is_array($recipients))
		{
			set_flash('danger', 'Harap pilih minimal satu pegawai penerima disposisi.');
			redirect('disposisi/buat/'.(int) $this->input->post('incoming_letter_id'));
			return;
		}

		if ( ! $fv->run())
		{
			$this->create((int) $this->input->post('incoming_letter_id'));
			return;
		}

		$disp_data = array(
			'incoming_letter_id' => $this->input->post('incoming_letter_id') ? (int) $this->input->post('incoming_letter_id') : NULL,
			'disposition_number' => $this->input->post('disposition_number', TRUE),
			'disposition_date'   => $this->input->post('disposition_date', TRUE),
			'deadline_date'      => $this->input->post('deadline_date', TRUE) ?: NULL,
			'priority'           => $this->input->post('priority', TRUE),
			'instruction'        => $this->input->post('instruction', TRUE),
			'notes'              => $this->input->post('notes', TRUE) ?: NULL,
			'status'             => 'menunggu',
			'created_by'         => (int) $this->current_user['id'],
		);

		$disp_id = $this->Disposition_model->insert($disp_data, $recipients);

		if ($disp_id)
		{
			// Kirim notifikasi ke setiap penerima disposisi
			foreach ($recipients as $uid)
			{
				send_notification(
					(int) $uid,
					'Disposisi Baru dari Kepala Sekolah',
					'Anda menerima instruksi disposisi No. '.$disp_data['disposition_number'].': "'.$disp_data['instruction'].'". Tenggat: '.($disp_data['deadline_date'] ? tgl_indo($disp_data['deadline_date']) : 'Segera'),
					site_url('disposisi-saya'),
					'disposisi'
				);
			}

			audit_log('DISPOSITION_CREATE', 'dispositions', $disp_id, 'Membuat disposisi '.$disp_data['disposition_number'].' kepada '.count($recipients).' pegawai.');
			set_flash('success', 'Lembar disposisi '.$disp_data['disposition_number'].' berhasil dibuat dan dikirim ke pegawai terkait.');
			redirect('disposisi/detail/'.$disp_id);
		}
		else
		{
			set_flash('danger', 'Gagal menyimpan disposisi.');
			redirect('disposisi');
		}
	}

	/** Detail lembar disposisi, status progres seluruh pegawai, dan riwayat. */
	public function detail($id = 0)
	{
		$disp = $this->Disposition_model->find((int) $id);
		if ( ! $disp)
		{
			$this->not_found('Lembar disposisi tidak ditemukan.');
		}

		// Keamanan IDOR: Pegawai biasa hanya boleh melihat disposisi jika dia adalah salah satu penerima!
		if ($this->is_pegawai())
		{
			$is_recipient = FALSE;
			foreach ($disp['recipients'] as $r)
			{
				if ((int) $r['user_id'] === (int) $this->current_user['id'] || (int) $r['delegated_to'] === (int) $this->current_user['id'])
				{
					$is_recipient = TRUE;
					break;
				}
			}
			if ( ! $is_recipient)
			{
				$this->forbidden('Anda tidak memiliki wewenang untuk melihat disposisi ini.');
			}
		}

		$this->render('dispositions/detail', array(
			'page_title'  => 'Detail Disposisi: '.$disp['disposition_number'],
			'disp'        => $disp,
			'employees'   => $this->User_model->get_active_employees(),
		));
	}

	/** Pembaruan progres tindak lanjut oleh pegawai penerima. */
	public function update_progress($recipient_id = 0)
	{
		$this->require_post();
		$recip = $this->Disposition_model->recipient_find((int) $recipient_id);
		if ( ! $recip)
		{
			$this->not_found('Data penerima disposisi tidak ditemukan.');
		}

		// Keamanan IDOR: Hanya pegawai penerima sendiri atau Kepala Sekolah / Superuser yang berwenang
		$uid = (int) $this->current_user['id'];
		if ( ! $this->has_role(array('kepala_sekolah', 'superuser', 'admin')) && (int) $recip['user_id'] !== $uid)
		{
			$this->forbidden('Anda hanya berhak memperbarui progres disposisi milik Anda sendiri.');
		}

		$status = $this->input->post('status', TRUE);
		$notes  = $this->input->post('progress_notes', TRUE);
		$pct    = (int) $this->input->post('progress_percent');

		if ($status === 'selesai')
		{
			$pct = 100;
		}

		$data = array(
			'status'           => $status,
			'progress_percent' => $pct,
			'progress_notes'   => $notes,
		);

		// Unggah bukti tindak lanjut jika ada (PDF/JPG/PNG max 5 MB)
		if ( ! empty($_FILES['evidence_file']['name']))
		{
			$upload = $this->handle_upload('evidence_file');
			if (isset($upload['error']))
			{
				set_flash('danger', 'Gagal mengunggah bukti: '.$upload['error']);
				redirect('disposisi/detail/'.$recip['disposition_id']);
				return;
			}
			$data['evidence_file'] = $upload['path'];
			$data['evidence_name'] = $upload['name'];
		}

		$this->Disposition_model->update_progress($recip['id'], $data, $uid);

		// Jika status selesai, beri tahu Kepala Sekolah
		if ($status === 'selesai' && $recip['created_by'])
		{
			send_notification(
				(int) $recip['created_by'],
				'Tindak Lanjut Disposisi Selesai',
				$this->current_user['full_name'].' telah menyelesaikan tindak lanjut untuk disposisi No. '.$recip['disposition_number'].'. Catatan: '.$notes,
				site_url('disposisi/detail/'.$recip['disposition_id']),
				'disposisi'
			);
		}

		audit_log('DISPOSITION_PROGRESS', 'disposition_recipients', $recip['id'], 'Update progres disposisi '.$recip['disposition_number'].': '.$status.' ('.$pct.'%)');
		set_flash('success', 'Laporan progres tindak lanjut berhasil disimpan.');

		if ($this->is_pegawai())
		{
			redirect('disposisi-saya');
		}
		else
		{
			redirect('disposisi/detail/'.$recip['disposition_id']);
		}
	}

	/** Delegasi ulang disposisi kepada pegawai lain. */
	public function delegate($recipient_id = 0)
	{
		$this->require_post();
		$recip = $this->Disposition_model->recipient_find((int) $recipient_id);
		if ( ! $recip)
		{
			$this->not_found('Data penerima disposisi tidak ditemukan.');
		}

		$uid = (int) $this->current_user['id'];
		if ( ! $this->has_role(array('kepala_sekolah', 'superuser', 'admin')) && (int) $recip['user_id'] !== $uid)
		{
			$this->forbidden('Anda tidak berhak mendelegasikan disposisi ini.');
		}

		$to_user_id = (int) $this->input->post('to_user_id');
		$reason     = trim((string) $this->input->post('delegation_reason', TRUE));

		if ( ! $to_user_id || $to_user_id === (int) $recip['user_id'])
		{
			set_flash('danger', 'Pilih pegawai tujuan delegasi yang berbeda.');
			redirect('disposisi/detail/'.$recip['disposition_id']);
			return;
		}

		if ($reason === '')
		{
			set_flash('danger', 'Alasan delegasi ulang wajib diisi.');
			redirect('disposisi/detail/'.$recip['disposition_id']);
			return;
		}

		$this->Disposition_model->delegate($recip['id'], $uid, $to_user_id, $reason);

		// Beri tahu pegawai penerima delegasi baru
		send_notification(
			$to_user_id,
			'Penerusan Delegasi Disposisi',
			'Anda menerima delegasi tugas disposisi No. '.$recip['disposition_number'].' dari '.$this->current_user['full_name'].'. Alasan: '.$reason,
			site_url('disposisi-saya'),
			'disposisi'
		);

		audit_log('DISPOSITION_DELEGATE', 'disposition_recipients', $recip['id'], 'Mendelegasikan disposisi '.$recip['disposition_number'].' ke user #'.$to_user_id.'. Alasan: '.$reason);
		set_flash('success', 'Disposisi berhasil didelegasikan kepada pegawai yang dipilih.');
		redirect('disposisi/detail/'.$recip['disposition_id']);
	}

	/** Unduh file bukti tindak lanjut secara aman. */
	public function evidence($recipient_id = 0)
	{
		$recip = $this->Disposition_model->recipient_find((int) $recipient_id);
		if ( ! $recip OR empty($recip['evidence_file']))
		{
			$this->not_found('Berkas bukti tidak ditemukan.');
		}

		// Validasi path aman di storage
		$base = realpath(storage_path('dispositions'));
		$path = realpath(storage_path($recip['evidence_file']));
		if ( ! $base OR ! $path OR strpos($path, $base.DIRECTORY_SEPARATOR) !== 0 OR ! is_file($path))
		{
			$this->not_found('File bukti tidak ditemukan di server.');
		}

		$mime = mime_content_type($path) ?: 'application/octet-stream';
		$inline = $this->input->get('lihat') === '1' || $mime === 'application/pdf' || strpos($mime, 'image/') === 0;

		$this->output
			->set_header('Content-Type: '.$mime)
			->set_header('Content-Disposition: '.($inline ? 'inline' : 'attachment').'; filename="'.safe_filename($recip['evidence_name'] ?: 'bukti_disposisi').'.'.pathinfo($path, PATHINFO_EXTENSION).'"')
			->set_header('Content-Length: '.filesize($path))
			->set_header('Cache-Control: private, no-store, max-age=0')
			->set_output(file_get_contents($path));
	}

	protected function handle_upload($field)
	{
		if (empty($_FILES[$field]['name']))
		{
			return array();
		}

		$dir = storage_path('dispositions');
		if ( ! is_dir($dir)) @mkdir($dir, 0755, TRUE);

		$this->load->library('upload');
		$this->upload->initialize(array(
			'upload_path'      => $dir,
			'allowed_types'    => 'pdf|png|jpg|jpeg|docx|xlsx',
			'max_size'         => 5120, // 5 MB
			'encrypt_name'     => TRUE,
			'file_ext_tolower' => TRUE,
		), TRUE);

		if ( ! $this->upload->do_upload($field))
		{
			return array('error' => strip_tags($this->upload->display_errors('', '')));
		}
		$up = $this->upload->data();
		return array(
			'path' => 'dispositions/'.$up['file_name'],
			'name' => $_FILES[$field]['name'],
		);
	}
}
