<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Incoming_letters extends Admin_Controller
{
	const PER_PAGE = 15;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Incoming_letter_model', 'Disposition_model'));
		$this->load->library('pagination');
		$this->require_role(array('tu', 'kepala_sekolah', 'superuser', 'admin'));
	}

	public function index()
	{
		$filters = array(
			'q'      => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
			'status' => (string) $this->input->get('status', TRUE),
			'from'   => (string) $this->input->get('from', TRUE),
			'to'     => (string) $this->input->get('to', TRUE),
		);

		$total  = $this->Incoming_letter_model->count_search($filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('surat-masuk', $total, self::PER_PAGE));

		$counts = array(
			'semua'       => $this->Incoming_letter_model->count_all(),
			'baru'        => $this->Incoming_letter_model->count_status('baru'),
			'diteruskan'  => $this->Incoming_letter_model->count_status('diteruskan'),
			'didisposisi' => $this->Incoming_letter_model->count_status('didisposisi'),
			'selesai'     => $this->Incoming_letter_model->count_status('selesai'),
		);

		$this->render('incoming/index', array(
			'page_title' => 'Surat Masuk',
			'letters'    => $this->Incoming_letter_model->search($filters, self::PER_PAGE, $offset),
			'total'      => $total,
			'filters'    => $filters,
			'counts'     => $counts,
			'offset'     => $offset,
			'pagination' => $this->pagination->create_links(),
		));
	}

	public function create()
	{
		$this->require_role(array('tu', 'superuser', 'admin'));
		$this->render('incoming/create', array(
			'page_title'    => 'Catat Surat Masuk',
			'agenda_number' => $this->Incoming_letter_model->generate_agenda_number(),
		));
	}

	public function store()
	{
		$this->require_role(array('tu', 'superuser', 'admin'));
		$this->require_post();

		$this->load->library('form_validation');
		$fv = $this->form_validation;
		$fv->set_rules('agenda_number', 'Nomor Agenda', 'trim|required|max_length[50]');
		$fv->set_rules('letter_number', 'Nomor Surat Asal', 'trim|required|max_length[100]');
		$fv->set_rules('letter_date', 'Tanggal Surat', 'required');
		$fv->set_rules('received_date', 'Tanggal Diterima', 'required');
		$fv->set_rules('sender', 'Asal / Pengirim', 'trim|required|max_length[200]');
		$fv->set_rules('recipient', 'Ditujukan Kepada', 'trim|max_length[200]');
		$fv->set_rules('subject', 'Perihal Surat', 'trim|required|max_length[255]');
		$fv->set_rules('description', 'Ringkasan Isi', 'trim');

		if ( ! $fv->run())
		{
			$this->render('incoming/create', array(
				'page_title'    => 'Catat Surat Masuk',
				'agenda_number' => $this->input->post('agenda_number'),
			));
			return;
		}

		$upload = $this->handle_upload('letter_file');
		if (isset($upload['error']))
		{
			set_flash('danger', 'Gagal mengunggah berkas: '.$upload['error']);
			$this->render('incoming/create', array(
				'page_title'    => 'Catat Surat Masuk',
				'agenda_number' => $this->input->post('agenda_number'),
			));
			return;
		}

		$data = array(
			'agenda_number' => $this->input->post('agenda_number', TRUE),
			'letter_number' => $this->input->post('letter_number', TRUE),
			'letter_date'   => $this->input->post('letter_date', TRUE),
			'received_date' => $this->input->post('received_date', TRUE),
			'sender'        => $this->input->post('sender', TRUE),
			'recipient'     => $this->input->post('recipient', TRUE) ?: 'Kepala Sekolah',
			'subject'       => $this->input->post('subject', TRUE),
			'description'   => $this->input->post('description', TRUE),
			'file_path'     => isset($upload['path']) ? $upload['path'] : NULL,
			'file_name'     => isset($upload['name']) ? $upload['name'] : NULL,
			'status'        => 'baru',
			'created_by'    => (int) $this->current_user['id'],
		);

		$id = $this->Incoming_letter_model->insert($data);
		if ($id)
		{
			audit_log('INCOMING_LETTER_CREATE', 'incoming_letters', $id, 'Mencatat surat masuk '.$data['agenda_number'].' dari '.$data['sender']);
			set_flash('success', 'Surat masuk berhasil dicatat dengan No. Agenda '.$data['agenda_number']);
			redirect('surat-masuk/detail/'.$id);
		}
		else
		{
			set_flash('danger', 'Gagal menyimpan surat masuk.');
			redirect('surat-masuk/catat');
		}
	}

	public function detail($id = 0)
	{
		$letter = $this->load_letter($id);
		$dispositions = $this->db->select('d.*, u.full_name AS creator_name,
				(SELECT COUNT(*) FROM disposition_recipients dr WHERE dr.disposition_id = d.id) AS total_recipients,
				(SELECT COUNT(*) FROM disposition_recipients dr2 WHERE dr2.disposition_id = d.id AND dr2.status = "selesai") AS completed_recipients')
			->from('dispositions d')
			->join('users u', 'u.id = d.created_by', 'left')
			->where('d.incoming_letter_id', (int) $letter['id'])
			->order_by('d.id', 'DESC')
			->get()->result_array();

		$kepsek = $this->db->get_where('users', array('role' => 'kepala_sekolah', 'is_active' => 1))->row_array();

		$this->render('incoming/detail', array(
			'page_title'   => 'Detail Surat Masuk: '.$letter['agenda_number'],
			'letter'       => $letter,
			'dispositions' => $dispositions,
			'kepsek'       => $kepsek,
		));
	}

	public function forward($id = 0)
	{
		$this->require_role(array('tu', 'superuser', 'admin'));
		$this->require_post();
		$letter = $this->load_letter($id);

		$this->Incoming_letter_model->update($letter['id'], array('status' => 'diteruskan'));
		audit_log('INCOMING_LETTER_FORWARD', 'incoming_letters', $letter['id'], 'Meneruskan surat masuk '.$letter['agenda_number'].' ke Kepala Sekolah');

		// Kirim notifikasi ke Kepala Sekolah
		$kepseks = $this->db->get_where('users', array('role' => 'kepala_sekolah', 'is_active' => 1))->result_array();
		foreach ($kepseks as $ks)
		{
			send_notification(
				$ks['id'],
				'Surat Masuk Baru Perlu Diperiksa',
				'Surat masuk '.$letter['agenda_number'].' dari "'.$letter['sender'].'" perihal "'.$letter['subject'].'" siap untuk diperiksa dan didisposisikan.',
				site_url('surat-masuk/detail/'.$letter['id']),
				'disposisi'
			);
		}

		set_flash('success', 'Surat masuk '.$letter['agenda_number'].' telah diteruskan ke Kepala Sekolah untuk disposisi.');
		redirect('surat-masuk/detail/'.$letter['id']);
	}

	public function edit($id = 0)
	{
		$this->require_role(array('tu', 'superuser', 'admin'));
		$letter = $this->load_letter($id);
		$this->render('incoming/edit', array(
			'page_title' => 'Edit Surat Masuk: '.$letter['agenda_number'],
			'letter'     => $letter,
		));
	}

	public function update($id = 0)
	{
		$this->require_role(array('tu', 'superuser', 'admin'));
		$this->require_post();
		$letter = $this->load_letter($id);

		$this->load->library('form_validation');
		$fv = $this->form_validation;
		$fv->set_rules('agenda_number', 'Nomor Agenda', 'trim|required|max_length[50]');
		$fv->set_rules('letter_number', 'Nomor Surat Asal', 'trim|required|max_length[100]');
		$fv->set_rules('letter_date', 'Tanggal Surat', 'required');
		$fv->set_rules('received_date', 'Tanggal Diterima', 'required');
		$fv->set_rules('sender', 'Asal / Pengirim', 'trim|required|max_length[200]');
		$fv->set_rules('recipient', 'Ditujukan Kepada', 'trim|max_length[200]');
		$fv->set_rules('subject', 'Perihal Surat', 'trim|required|max_length[255]');

		if ( ! $fv->run())
		{
			$this->render('incoming/edit', array(
				'page_title' => 'Edit Surat Masuk: '.$letter['agenda_number'],
				'letter'     => array_merge($letter, $this->input->post()),
			));
			return;
		}

		$data = array(
			'agenda_number' => $this->input->post('agenda_number', TRUE),
			'letter_number' => $this->input->post('letter_number', TRUE),
			'letter_date'   => $this->input->post('letter_date', TRUE),
			'received_date' => $this->input->post('received_date', TRUE),
			'sender'        => $this->input->post('sender', TRUE),
			'recipient'     => $this->input->post('recipient', TRUE),
			'subject'       => $this->input->post('subject', TRUE),
			'description'   => $this->input->post('description', TRUE),
		);

		if ( ! empty($_FILES['letter_file']['name']))
		{
			$upload = $this->handle_upload('letter_file');
			if (isset($upload['error']))
			{
				set_flash('danger', 'Gagal mengunggah berkas baru: '.$upload['error']);
				redirect('surat-masuk/edit/'.$letter['id']);
				return;
			}
			$data['file_path'] = $upload['path'];
			$data['file_name'] = $upload['name'];

			if ($letter['file_path'])
			{
				$old = storage_path($letter['file_path']);
				if (is_file($old)) @unlink($old);
			}
		}

		$this->Incoming_letter_model->update($letter['id'], $data);
		audit_log('INCOMING_LETTER_UPDATE', 'incoming_letters', $letter['id'], 'Memperbarui data surat masuk '.$letter['agenda_number']);
		set_flash('success', 'Surat masuk berhasil diperbarui.');
		redirect('surat-masuk/detail/'.$letter['id']);
	}

	public function delete($id = 0)
	{
		$this->require_role(array('tu', 'superuser', 'admin'));
		$this->require_post();
		$letter = $this->load_letter($id);

		$this->Incoming_letter_model->delete($letter['id']);
		audit_log('INCOMING_LETTER_DELETE', 'incoming_letters', $letter['id'], 'Menghapus surat masuk '.$letter['agenda_number']);
		set_flash('success', 'Surat masuk '.$letter['agenda_number'].' telah dihapus.');
		redirect('surat-masuk');
	}

	public function file($id = 0)
	{
		$letter = $this->load_letter($id);
		if (empty($letter['file_path']))
		{
			$this->not_found('Berkas surat tidak ditemukan.');
		}

		$base = realpath(storage_path('incoming_letters'));
		$path = realpath(storage_path($letter['file_path']));
		if ( ! $base OR ! $path OR strpos($path, $base.DIRECTORY_SEPARATOR) !== 0 OR ! is_file($path))
		{
			$this->not_found('File berkas fisik tidak ditemukan di server.');
		}

		$mime = mime_content_type($path) ?: 'application/octet-stream';
		$inline = $this->input->get('lihat') === '1' || $mime === 'application/pdf' || strpos($mime, 'image/') === 0;

		$this->output
			->set_header('Content-Type: '.$mime)
			->set_header('Content-Disposition: '.($inline ? 'inline' : 'attachment').'; filename="'.safe_filename($letter['file_name'] ?: $letter['agenda_number']).'.'.pathinfo($path, PATHINFO_EXTENSION).'"')
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

		$dir = storage_path('incoming_letters');
		if ( ! is_dir($dir)) @mkdir($dir, 0755, TRUE);

		$this->load->library('upload');
		$this->upload->initialize(array(
			'upload_path'      => $dir,
			'allowed_types'    => 'pdf|png|jpg|jpeg',
			'max_size'         => 2048, // 2 MB
			'encrypt_name'     => TRUE,
			'file_ext_tolower' => TRUE,
		), TRUE);

		if ( ! $this->upload->do_upload($field))
		{
			return array('error' => strip_tags($this->upload->display_errors('', '')));
		}
		$up = $this->upload->data();
		return array(
			'path' => 'incoming_letters/'.$up['file_name'],
			'name' => $_FILES[$field]['name'],
		);
	}

	protected function load_letter($id)
	{
		$letter = $this->Incoming_letter_model->find((int) $id);
		if ( ! $letter)
		{
			$this->not_found('Surat masuk tidak ditemukan.');
		}
		return $letter;
	}
}
