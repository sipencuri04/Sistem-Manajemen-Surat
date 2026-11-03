<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul surat: daftar, pembuatan, pratinjau, simpan, edit, hapus, PDF.
 */
class Letters extends Admin_Controller
{
	const PER_PAGE = 10;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Letter_model', 'Letter_type_model', 'Letter_file_model'));
		$this->load->library(array('Template_registry', 'Letter_numbering', 'Letter_renderer', 'pagination'));
		$this->require_role(array('tu', 'kepala_sekolah', 'superuser', 'admin'));
	}

	/* =====================================================================
	 * DATA SURAT
	 * ===================================================================== */
	public function index()
	{
		$filters = array(
			'q'      => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
			'type'   => (int) $this->input->get('type'),
			'status' => (string) $this->input->get('status', TRUE),
		);
		$total  = $this->Letter_model->count_search($filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;

		$this->pagination->initialize(pagination_config('surat', $total, self::PER_PAGE));

		$this->render('letters/index', array(
			'page_title' => 'Data Surat',
			'filters'    => $filters,
			'letters'    => $this->Letter_model->search($filters, self::PER_PAGE, $offset),
			'total'      => $total,
			'offset'     => $offset,
			'types'      => $this->Letter_type_model->all(),
			'pagination' => $this->pagination->create_links(),
		));
	}

	/* =====================================================================
	 * PEMBUATAN SURAT
	 * ===================================================================== */

	/** Langkah 1: pilih jenis surat. */
	public function select_type()
	{
		$this->render('letters/select_type', array(
			'page_title' => 'Buat Surat',
			'types'      => $this->Letter_type_model->all(TRUE),
			'registry'   => $this->template_registry,
		));
	}

	/** Langkah 2-3: formulir sesuai jenis surat. */
	public function create($type_id = 0)
	{
		list($type, $tpl) = $this->load_type($type_id, TRUE);

		$defaults = array(
			'letter_date'     => date('Y-m-d'),
			'place'           => $this->school['city'],
			'subject'         => $tpl['default_subject'],
			'recipient'       => $tpl['default_recipient'],
			'signer_title'    => $this->school['signer_title'],
			'signer_name'     => $this->school['headmaster_name'],
			'signer_nip'      => $this->school['headmaster_nip'],
			'number_mode'     => 'auto',
			'attachment_text' => $tpl['attachment'] ? '-' : '',
		);

		// Dibuka dari menu Permohonan Siswa: isi formulir dengan data dari siswa.
		$f = array();
		$req = $this->pending_request((int) $this->input->get('permohonan'), $type);
		if ($req)
		{
			$f = $req['data'];
			if (empty($f['nama_siswa']) && ! empty($req['requester_name']))
			{
				$f['nama_siswa'] = $req['requester_name'];
			}
			if (empty($f['kelas']) && ! empty($req['requester_class']))
			{
				$f['kelas'] = $req['requester_class'];
			}
			if (empty($f['kompetensi_keahlian']) && ! empty($f['jurusan']))
			{
				$f['kompetensi_keahlian'] = $f['jurusan'];
			}
			if (empty($f['alamat_instansi']) && ! empty($f['alamat']))
			{
				$f['alamat_instansi'] = $f['alamat'];
			}

			$defaults['notes'] = 'Dari permohonan siswa kode '.$req['tracking_code'];
			if ($req['requester_note'])
			{
				$defaults['notes'] .= ' (catatan: '.mb_substr($req['requester_note'], 0, 300).')';
			}
		}

		$this->show_form('create', $type, $tpl, $defaults, $f, array());
	}

	/** @var array|null permohonan siswa yang sedang diproses (untuk formulir) */
	protected $view_request = NULL;

	/** Ambil permohonan siswa yang masih menunggu dan cocok dengan jenis surat. */
	protected function pending_request($request_id, array $type)
	{
		if ($request_id <= 0 OR ! $this->schema_ready)
		{
			return NULL;
		}
		$this->load->model('Letter_request_model');
		$req = $this->Letter_request_model->find($request_id);
		if ( ! $req OR $req['status'] !== 'pending' OR (int) $req['letter_type_id'] !== (int) $type['id'])
		{
			return NULL;
		}
		$this->view_request = $req;
		return $req;
	}

	/** Langkah 6: simpan surat baru. */
	public function store($type_id = 0)
	{
		$this->require_post();
		list($type, $tpl) = $this->load_type($type_id, TRUE);
		$this->pending_request((int) $this->input->post('request_id'), $type);
		list($row, $f_errors, $valid) = $this->process_input($type, $tpl, NULL);

		if ( ! $valid)
		{
			$this->show_form('create', $type, $tpl, $this->input->post(), $this->input->post('f'), $f_errors);
			return;
		}

		$row['letter_type_id'] = (int) $type['id'];
		$save_as               = $this->input->post('save_as');
		$can_sign = in_array(strtolower($this->current_user['role']), array('kepala_sekolah', 'superuser'), TRUE);
		// TU dan Pegawai tidak bisa mengesahkan tanda tangan sendiri, wajib diajukan ke Kepala Sekolah
		if ($save_as === 'final' && ! $can_sign) {
			$save_as = 'approval';
		}
		
		if ($save_as === 'approval')
		{
			$row['status']          = 'draft';
			$row['approval_status'] = 'menunggu_persetujuan';
		}
		elseif ($save_as === 'final')
		{
			$row['status']          = 'final';
			$row['approval_status'] = 'disetujui';
		}
		else
		{
			$row['status']          = 'draft';
			$row['approval_status'] = 'draft';
		}
		$row['created_by']     = (int) $this->current_user['id'];
		$req = $this->view_request;

		$id = $this->insert_with_number($row, $type);
		if ( ! $id)
		{
			set_flash('danger', 'Surat gagal disimpan. Silakan coba lagi.');
			$this->show_form('create', $type, $tpl, $this->input->post(), $this->input->post('f'), $f_errors);
			return;
		}

		$letter = $this->Letter_model->find($id);
		$msg = 'Surat berhasil disimpan dengan nomor '.$letter['letter_number'].'.';
		if ($letter['status'] === 'final')
		{
			$msg .= $this->archive_pdf($letter) ? ' PDF arsip telah dibuat.' : ' (PDF arsip gagal dibuat, silakan klik "Buat Ulang PDF".)';
		}
		if ($req)
		{
			$this->Letter_request_model->update($req['id'], array(
				'status'       => 'approved',
				'letter_id'    => $id,
				'admin_note'   => 'Surat sudah terbit. Silakan ambil di ruang TU pada jam kerja dengan menyebutkan kode permohonan.',
				'processed_by' => (int) $this->current_user['id'],
				'processed_at' => date('Y-m-d H:i:s'),
			));
			$msg .= ' Permohonan '.$req['tracking_code'].' kini berstatus Siap Diambil.';
		}

		if ($row['approval_status'] === 'menunggu_persetujuan')
		{
			$kepseks = $this->db->get_where('users', array('role' => 'kepala_sekolah', 'is_active' => 1))->result_array();
			foreach ($kepseks as $ks)
			{
				send_notification(
					(int) $ks['id'],
					'Surat Menunggu Persetujuan',
					'Surat baru No. '.$letter['letter_number'].' perihal "'.$letter['subject'].'" diajukan oleh '.$this->current_user['full_name'].' untuk persetujuan.',
					site_url('persetujuan-surat'),
					'persetujuan'
				);
			}
			$msg .= ' Surat telah diajukan ke Kepala Sekolah untuk persetujuan.';
		}

		audit_log('LETTER_CREATE', 'letters', $id, 'Membuat surat '.$letter['letter_number'].' (Status: '.$row['status'].', Approval: '.$row['approval_status'].')');
		set_flash('success', $msg);
		redirect('surat/detail/'.$id);
	}

	/**
	 * Simpan surat dan ambil nomor dalam satu transaksi (mencegah nomor ganda).
	 */
	protected function insert_with_number(array $row, array $type)
	{
		$debug = $this->db->db_debug;
		$this->db->db_debug = FALSE;

		for ($attempt = 0; $attempt < 3; $attempt++)
		{
			$this->db->trans_begin();
			try
			{
				if ($row['number_mode'] === 'auto')
				{
					$alloc = $this->letter_numbering->allocate($this->school, $type, $row['letter_date']);
					$row['letter_number'] = $alloc['number'];
					$row['sequence_no']   = $alloc['sequence'];
					$row['number_year']   = $alloc['year'];
				}
				$id = $this->Letter_model->insert($row);
				if ( ! $id OR $this->db->trans_status() === FALSE)
				{
					throw new RuntimeException('Insert gagal: '.json_encode($this->db->error()));
				}
				$this->db->trans_commit();
				$this->db->db_debug = $debug;
				return $id;
			}
			catch (Exception $ex)
			{
				$this->db->trans_rollback();
				log_message('error', 'Simpan surat gagal (percobaan '.($attempt + 1).'): '.$ex->getMessage());
				if ($row['number_mode'] === 'manual')
				{
					break; // nomor manual bentrok: jangan ulangi
				}
			}
		}
		$this->db->db_debug = $debug;
		return 0;
	}

	/** Langkah 5: pratinjau sebelum disimpan (dibuka di tab baru). */
	public function preview()
	{
		$this->require_post();
		$type_id = (int) $this->input->post('letter_type_id');
		$letter_id = (int) $this->input->post('letter_id');
		list($type, $tpl) = $this->load_type($type_id, FALSE);
		list($row, $f_errors, $valid) = $this->process_input($type, $tpl, NULL, TRUE);

		if ($letter_id && ($existing = $this->Letter_model->find($letter_id)))
		{
			$number = $row['number_mode'] === 'manual' ? $row['letter_number'] : $existing['letter_number'];
		}
		elseif ($row['number_mode'] === 'manual')
		{
			$number = $row['letter_number'];
		}
		else
		{
			$number = $this->letter_numbering->preview($this->school, $type, $row['letter_date']);
		}
		$row['letter_number'] = $number;
		$row['data'] = $row['data_array'];

		$html = $this->letter_renderer->html($row, $type, $this->school, 'html', array('watermark' => 'PRATINJAU'));
		$this->load->view('letters/preview', array(
			'html'     => $html,
			'errors'   => $valid ? array() : array_merge(array_values($this->form_validation->error_array()), array_values($f_errors)),
			'is_auto'  => ! $letter_id && $row['number_mode'] === 'auto',
		));
	}

	/** AJAX: perkiraan nomor berikutnya untuk tanggal tertentu. */
	public function next_number($type_id = 0)
	{
		list($type) = $this->load_type($type_id, FALSE);
		$date = (string) $this->input->get('date');
		if ( ! valid_date_string($date))
		{
			$date = date('Y-m-d');
		}
		$this->output->set_content_type('application/json')
			->set_output(json_encode(array('number' => $this->letter_numbering->preview($this->school, $type, $date))));
	}

	/* =====================================================================
	 * DETAIL / EDIT / HAPUS
	 * ===================================================================== */
	public function detail($id = 0)
	{
		$letter = $this->load_letter($id);
		list($type, $tpl) = $this->load_type($letter['letter_type_id'], FALSE);

		$this->render('letters/detail', array(
			'page_title' => 'Detail Surat',
			'letter'     => $letter,
			'type'       => $type,
			'tpl'        => $tpl,
			'values'     => $this->template_registry->formatted_values($tpl, $letter['data']),
			'files'      => $this->Letter_file_model->by_letter($letter['id']),
		));
	}

	public function edit($id = 0)
	{
		$letter = $this->load_letter($id);
		list($type, $tpl) = $this->load_type($letter['letter_type_id'], FALSE);
		$values = $letter;
		$values['number_mode'] = 'keep';
		$this->show_form('edit', $type, $tpl, $values, $letter['data'], array(), $letter);
	}

	public function update($id = 0)
	{
		$this->require_post();
		$letter = $this->load_letter($id);
		list($type, $tpl) = $this->load_type($letter['letter_type_id'], FALSE);
		list($row, $f_errors, $valid) = $this->process_input($type, $tpl, $letter);

		if ( ! $valid)
		{
			$this->show_form('edit', $type, $tpl, $this->input->post(), $this->input->post('f'), $f_errors, $letter);
			return;
		}

		if ($row['number_mode'] === 'keep')
		{
			unset($row['letter_number'], $row['number_mode'], $row['sequence_no'], $row['number_year']);
		}
		$save_as = $this->input->post('save_as');
		$can_sign = in_array(strtolower($this->current_user['role']), array('kepala_sekolah', 'superuser'), TRUE);
		// TU dan Pegawai tidak bisa mengesahkan tanda tangan sendiri, wajib diajukan ke Kepala Sekolah
		if ($save_as === 'final' && ! $can_sign) {
			$save_as = 'approval';
		}
		
		if ($save_as === 'approval')
		{
			$row['status']          = 'draft';
			$row['approval_status'] = 'menunggu_persetujuan';
		}
		elseif ($save_as === 'final')
		{
			$row['status']          = 'final';
			$row['approval_status'] = 'disetujui';
		}
		elseif ($save_as === 'draft')
		{
			$row['status']          = 'draft';
		}
		else
		{
			$row['status']          = $letter['status'];
		}
		$row['updated_by'] = (int) $this->current_user['id'];

		$debug = $this->db->db_debug;
		$this->db->db_debug = FALSE;
		$ok = $this->Letter_model->update($letter['id'], $row);
		$this->db->db_debug = $debug;

		if ( ! $ok)
		{
			log_message('error', 'Update surat #'.$letter['id'].' gagal: '.json_encode($this->db->error()));
			set_flash('danger', 'Perubahan gagal disimpan. Pastikan nomor surat tidak duplikat.');
			$this->show_form('edit', $type, $tpl, $this->input->post(), $this->input->post('f'), $f_errors, $letter);
			return;
		}

		$fresh = $this->Letter_model->find($letter['id']);
		$msg = 'Perubahan surat berhasil disimpan.';

		if (isset($row['approval_status']) && $row['approval_status'] === 'menunggu_persetujuan')
		{
			$kepseks = $this->db->get_where('users', array('role' => 'kepala_sekolah', 'is_active' => 1))->result_array();
			foreach ($kepseks as $ks)
			{
				send_notification(
					(int) $ks['id'],
					'Surat Menunggu Persetujuan',
					'Surat No. '.$fresh['letter_number'].' perihal "'.$fresh['subject'].'" diajukan/diperbarui oleh '.$this->current_user['full_name'].' untuk persetujuan.',
					site_url('persetujuan-surat'),
					'persetujuan'
				);
			}
			$msg .= ' Surat telah diajukan ke Kepala Sekolah untuk persetujuan.';
		}

		// PDF arsip dibuat ulang agar sesuai data terbaru.
		if ($fresh['status'] === 'final' OR $this->Letter_file_model->latest_pdf($fresh['id']))
		{
			$msg .= $this->archive_pdf($fresh) ? ' PDF arsip diperbarui.' : ' (PDF arsip gagal diperbarui.)';
		}
		set_flash('success', $msg);
		redirect('surat/detail/'.$letter['id']);
	}

	public function delete($id = 0)
	{
		$this->require_post();
		$letter = $this->load_letter($id);

		$this->db->trans_start();
		$this->Letter_file_model->delete_by_letter($letter['id']);
		$this->Letter_model->delete($letter['id']);
		$this->db->trans_complete();

		if ($this->db->trans_status())
		{
			audit_log('LETTER_DELETE', 'letters', $letter['id'], 'Menghapus surat '.$letter['letter_number']);
			log_message('info', 'Surat '.$letter['letter_number'].' dihapus oleh '.$this->current_user['username']);
			set_flash('success', 'Surat '.$letter['letter_number'].' telah dihapus.');
		}
		else
		{
			set_flash('danger', 'Surat gagal dihapus.');
		}
		$back = $this->input->post('back') === 'arsip' ? 'arsip' : 'surat';
		redirect($back);
	}

	/* =====================================================================
	 * PDF
	 * ===================================================================== */

	/** Hasilkan PDF on-the-fly. $mode: 'unduh' (attachment) | 'lihat' (inline). */
	public function pdf($id = 0, $mode = 'unduh')
	{
		$letter = $this->load_letter($id);
		list($type) = $this->load_type($letter['letter_type_id'], FALSE);
		try
		{
			$mode_ttd = $this->input->get('mode_ttd');
			$opts = array(
				'watermark'       => $letter['status'] === 'draft' ? 'DRAFT' : NULL,
				'blank_signature' => ($mode_ttd === 'basah'),
			);
			$html = $this->letter_renderer->html($letter, $type, $this->school, 'pdf', $opts);
			$this->letter_renderer->stream($html, $letter['type_code'].'_'.$letter['letter_number'], $mode !== 'lihat');
		}
		catch (Exception $ex)
		{
			log_message('error', 'Gagal membuat PDF surat #'.$letter['id'].': '.$ex->getMessage());
			show_error('PDF gagal dibuat. Periksa log aplikasi (application/logs).', 500, 'Kesalahan PDF');
		}
	}

	/** Buat ulang & simpan PDF arsip ke storage. */
	public function regenerate($id = 0)
	{
		$this->require_post();
		$letter = $this->load_letter($id);
		if ($this->archive_pdf($letter))
		{
			set_flash('success', 'PDF arsip berhasil dibuat ulang.');
		}
		else
		{
			set_flash('danger', 'PDF arsip gagal dibuat. Pastikan folder storage/letters dapat ditulisi.');
		}
		redirect('surat/detail/'.$letter['id']);
	}

	/**
	 * Simpan file PDF ke storage/letters/YYYY/MM/ dan catat path-nya di letter_files.
	 * Versi lama diganti agar penyimpanan tetap ringkas.
	 */
	protected function archive_pdf(array $letter)
	{
		try
		{
			list($type) = $this->load_type($letter['letter_type_id'], FALSE);
			$html = $this->letter_renderer->html($letter, $type, $this->school, 'pdf', array(
				'watermark' => $letter['status'] === 'draft' ? 'DRAFT' : NULL,
			));
			$binary = $this->letter_renderer->pdf_string($html);

			$dir_rel = 'letters/'.date('Y', strtotime($letter['letter_date'])).'/'.date('m', strtotime($letter['letter_date']));
			$dir_abs = storage_path($dir_rel);
			if ( ! is_dir($dir_abs) && ! @mkdir($dir_abs, 0755, TRUE))
			{
				throw new RuntimeException('Tidak dapat membuat folder '.$dir_abs);
			}
			$name = 'surat-'.(int) $letter['id'].'-'.bin2hex(random_bytes(6)).'.pdf';
			if (file_put_contents($dir_abs.DIRECTORY_SEPARATOR.$name, $binary) === FALSE)
			{
				throw new RuntimeException('Tidak dapat menulis file PDF.');
			}

			$this->db->trans_start();
			$this->Letter_file_model->delete_by_letter($letter['id']);
			$this->Letter_file_model->add(array(
				'letter_id'     => (int) $letter['id'],
				'file_type'     => 'pdf',
				'file_path'     => $dir_rel.'/'.$name,
				'original_name' => safe_filename($letter['type_code'].'_'.$letter['letter_number']).'.pdf',
				'mime_type'     => 'application/pdf',
				'file_size'     => strlen($binary),
				'checksum'      => hash('sha256', $binary),
				'created_by'    => (int) $this->current_user['id'],
			));
			$this->db->trans_complete();
			return $this->db->trans_status();
		}
		catch (Exception $ex)
		{
			log_message('error', 'Arsip PDF surat #'.$letter['id'].' gagal: '.$ex->getMessage());
			return FALSE;
		}
	}

	/* =====================================================================
	 * UTILITAS
	 * ===================================================================== */

	protected function load_letter($id)
	{
		$letter = $this->Letter_model->find((int) $id);
		if ( ! $letter)
		{
			$this->not_found('Surat tidak ditemukan atau sudah dihapus.');
		}
		return $letter;
	}

	/** @return array [jenis surat (+teks template), konfigurasi template] */
	protected function load_type($type_id, $must_be_active)
	{
		$type = $this->Letter_type_model->find_with_template((int) $type_id);
		if ( ! $type OR ($must_be_active && ! (int) $type['is_active']))
		{
			$this->not_found('Jenis surat tidak ditemukan atau tidak aktif.');
		}
		$tpl = $this->template_registry->get($type['template_key']);
		if ( ! $tpl)
		{
			show_error('Template "'.e($type['template_key']).'" untuk jenis surat ini tidak ditemukan.', 500);
		}
		return array($type, $tpl);
	}

	/**
	 * Validasi server-side seluruh input formulir surat.
	 *
	 * @return array [$row, $field_errors, $valid]
	 */
	protected function process_input(array $type, array $tpl, $letter = NULL, $preview = FALSE)
	{
		$fv = $this->form_validation;
		$fv->reset_validation();
		$fv->set_data($this->input->post());

		$fv->set_rules('letter_date', 'Tanggal surat', 'required|callback__valid_date');
		$fv->set_rules('place', 'Tempat surat', 'trim|required|max_length[100]');
		$fv->set_rules('subject', 'Perihal', 'trim|required|max_length[255]');
		$fv->set_rules('signer_title', 'Jabatan penandatangan', 'trim|required|max_length[150]');
		$fv->set_rules('signer_name', 'Nama penandatangan', 'trim|required|max_length[150]');
		$fv->set_rules('signer_nip', 'NIP penandatangan', 'trim|max_length[30]');
		$fv->set_rules('notes', 'Catatan', 'trim|max_length[500]');
		if ($tpl['attachment'])
		{
			$fv->set_rules('attachment_text', 'Lampiran', 'trim|max_length[100]');
		}
		if ($tpl['recipient'] !== 'hidden')
		{
			$fv->set_rules('recipient', 'Tujuan surat', 'trim|max_length[255]'.($tpl['recipient'] === 'required' ? '|required' : ''));
			$fv->set_rules('recipient_address', 'Alamat tujuan', 'trim|max_length[255]');
		}

		$modes = $letter ? array('keep', 'manual') : array('auto', 'manual');
		$mode  = $this->input->post('number_mode');
		$mode  = in_array($mode, $modes, TRUE) ? $mode : $modes[0];
		if ($mode === 'manual')
		{
			$this->_except_id = $letter ? (int) $letter['id'] : 0;
			$fv->set_rules('letter_number', 'Nomor surat manual',
				'trim|required|max_length[100]|regex_match[/^[A-Za-z0-9\/\.\-_ ]+$/]|callback__unique_number',
				array('regex_match' => '{field} hanya boleh berisi huruf, angka, spasi, dan karakter / . - _'));
		}

		$valid = $fv->run();
		list($data, $f_errors) = $this->template_registry->collect($tpl, $this->input->post('f'));
		$valid = $valid && empty($f_errors);

		$post = function ($key) { return trim((string) $this->input->post($key)); };
		$letter_date = valid_date_string($post('letter_date')) ? $post('letter_date') : date('Y-m-d');

		$recipient = $tpl['recipient'] !== 'hidden' ? $post('recipient') : '';
		if ($recipient === '' && $tpl['recipient_field'] && ! empty($data[$tpl['recipient_field']]))
		{
			$recipient = (string) $data[$tpl['recipient_field']];
		}

		$row = array(
			'letter_date'       => $letter_date,
			'place'             => $post('place'),
			'subject'           => $post('subject'),
			'attachment_text'   => $tpl['attachment'] ? $post('attachment_text') : NULL,
			'recipient'         => $recipient !== '' ? $recipient : NULL,
			'recipient_address' => $tpl['recipient'] !== 'hidden' && $post('recipient_address') !== '' ? $post('recipient_address') : NULL,
			'signer_title'      => $post('signer_title'),
			'signer_name'       => $post('signer_name'),
			'signer_nip'        => $post('signer_nip'),
			'notes'             => $post('notes') !== '' ? $post('notes') : NULL,
			'signature_type'    => in_array($post('signature_type'), array('digital', 'basah'), TRUE) ? $post('signature_type') : 'digital',
			'stamp_type'        => ($letter && isset($letter['stamp_type']) && isset($letter['approval_status']) && $letter['approval_status'] === 'disetujui') ? $letter['stamp_type'] : 'belum_stempel',
			'stamped_by'        => ($letter && isset($letter['stamped_by']) && isset($letter['approval_status']) && $letter['approval_status'] === 'disetujui') ? $letter['stamped_by'] : NULL,
			'stamped_at'        => ($letter && isset($letter['stamped_at']) && isset($letter['approval_status']) && $letter['approval_status'] === 'disetujui') ? $letter['stamped_at'] : NULL,
			'content_data'      => json_encode($data, JSON_UNESCAPED_UNICODE),
			'data_array'        => $data,
			'number_mode'       => $mode,
			'letter_number'     => $mode === 'manual' ? $post('letter_number') : NULL,
			'sequence_no'       => NULL,
			'number_year'       => $this->letter_numbering->number_year($this->school, $letter_date),
			'status'            => $letter ? $letter['status'] : 'draft',
		);
		if ( ! $preview)
		{
			unset($row['data_array']);
		}
		return array($row, $f_errors, $valid);
	}

	/** @internal dipakai validasi nomor manual */
	protected $_except_id = 0;

	public function _valid_date($value)
	{
		if ( ! valid_date_string((string) $value))
		{
			$this->form_validation->set_message('_valid_date', '{field} tidak valid.');
			return FALSE;
		}
		return TRUE;
	}

	public function _unique_number($value)
	{
		if ($this->Letter_model->number_exists(trim((string) $value), $this->_except_id))
		{
			$this->form_validation->set_message('_unique_number', 'Nomor surat "'.$value.'" sudah digunakan. Gunakan nomor lain.');
			return FALSE;
		}
		return TRUE;
	}

	protected function show_form($mode, array $type, array $tpl, $values, $f_values, array $f_errors, $letter = NULL)
	{
		$values = is_array($values) ? $values : array();
		$this->render($mode === 'edit' ? 'letters/edit' : 'letters/create', array(
			'page_title'  => $mode === 'edit' ? 'Edit Surat' : 'Buat '.$type['name'],
			'mode'        => $mode,
			'type'        => $type,
			'tpl'         => $tpl,
			'val'         => $values,
			'f'           => is_array($f_values) ? $f_values : array(),
			'f_errors'    => $f_errors,
			'letter'      => $letter,
			'request'     => $this->view_request,
			'next_number' => $mode === 'create'
				? $this->letter_numbering->preview($this->school, $type, ! empty($values['letter_date']) && valid_date_string($values['letter_date']) ? $values['letter_date'] : date('Y-m-d'))
				: NULL,
		));
	}
	/** Pembubuhan stempel resmi sekolah oleh Tata Usaha (TU). */
	public function stempel($id = 0)
	{
		$this->require_post();
		$this->require_role(array('tu', 'superuser', 'admin'));

		$letter = $this->Letter_model->find((int) $id);
		if ( ! $letter)
		{
			$this->not_found('Surat tidak ditemukan.');
		}

		// Validasi Wewenang: TU hanya boleh membubuhkan stempel jika surat sudah disetujui/ditandatangani Kepala Sekolah
		if ( ! isset($letter['approval_status']) || $letter['approval_status'] !== 'disetujui') {
			set_flash('danger', 'Surat belum ditandatangani oleh Kepala Sekolah. Stempel TU hanya dapat dibubuhkan setelah surat disahkan Kepala Sekolah.');
			redirect('surat/detail/'.$letter['id']);
			return;
		}
		

		$stamp_action = (string) $this->input->post('stamp_action', TRUE);
		if ($stamp_action === 'digital')
		{
			$new_stamp = 'digital';
			$msg = 'Stempel Digital resmi sekolah berhasil dibubuhkan oleh Tata Usaha.';
		}
		elseif ($stamp_action === 'basah')
		{
			$new_stamp = 'basah';
			$msg = 'Surat ditandai untuk dicap stempel basah fisik oleh Tata Usaha.';
		}
		else
		{
			$new_stamp = 'belum_stempel';
			$msg = 'Stempel resmi surat dicabut oleh Tata Usaha.';
		}

		$this->db->where('id', $letter['id'])->update('letters', array(
			'stamp_type' => $new_stamp,
			'stamped_by' => $new_stamp !== 'belum_stempel' ? (int) $this->current_user['id'] : NULL,
			'stamped_at' => $new_stamp !== 'belum_stempel' ? date('Y-m-d H:i:s') : NULL,
			'updated_at' => date('Y-m-d H:i:s'),
		));

		// Perbarui PDF arsip jika surat sudah berstatus final
		if ($letter['status'] === 'final')
		{
			$updated_letter = $this->Letter_model->find($letter['id']);
			$this->archive_pdf($updated_letter);
		}

		audit_log('LETTER_STAMP', 'letters', $letter['id'], 'TU memperbarui stempel (' . $new_stamp . ') pada surat ' . $letter['letter_number']);
		set_flash('success', $msg);
		redirect('surat/detail/'.$letter['id']);
	}
}