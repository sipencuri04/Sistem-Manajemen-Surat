<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Layanan Kirim Surat Eksternal (Instansi / Lembaga Luar).
 * Memungkinkan dinas, instansi pemerintah, DUDI, sekolah lain mengunggah surat dinas (PDF/Gambar).
 */
class Kirim_surat extends MY_Controller
{
	const MAX_PER_HOUR_IP = 5;
	const MAX_PER_DAY_IP  = 15;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Incoming_letter_model', 'User_model'));
		$this->output->set_header('X-Frame-Options: DENY');
		$this->output->set_header('X-Content-Type-Options: nosniff');
	}

	/** Formulir pengiriman surat masuk dari instansi luar. */
	public function index()
	{
		$this->show_form();
	}

	/** Proses simpan berkas dan kirim ke antrean Surat Masuk TU. */
	public function simpan()
	{
		$this->require_post();
		$ip = $this->input->ip_address();

		// 1. Honeypot check
		if (trim((string) $this->input->post('website')) !== '')
		{
			log_message('error', 'Kirim surat eksternal ditolak (honeypot) dari '.$ip);
			show_error('Permohonan tidak dapat diproses.', 400);
		}

		// 2. Rate Limiting per IP
		$recent_count = $this->db->where('is_external', 1)
			->where('created_at >=', date('Y-m-d H:i:s', time() - 3600))
			->count_all_results('incoming_letters');
		if ($recent_count >= 10)
		{
			show_error('Terlalu banyak pengiriman dalam waktu singkat. Silakan coba lagi beberapa saat lagi.', 429, 'Batas Pengiriman');
		}

		// 3. Captcha check
		$a = (int) $this->session->userdata('ext_c_a');
		$b = (int) $this->session->userdata('ext_c_b');
		$ans = trim((string) $this->input->post('captcha'));
		if ($a === 0 || $b === 0 || $ans === '' || (int) $ans !== ($a + $b))
		{
			$this->show_form(array('captcha' => 'Jawaban hitungan belum benar. Silakan hitung ulang.'));
			return;
		}

		// 4. Form validation
		$this->load->library('form_validation');
		$fv = $this->form_validation;
		$fv->set_rules('sender', 'Nama Lembaga / Instansi Pengirim', 'trim|required|max_length[200]');
		$fv->set_rules('letter_number', 'Nomor Surat Resmi', 'trim|required|max_length[100]');
		$fv->set_rules('letter_date', 'Tanggal Surat', 'required');
		$fv->set_rules('subject', 'Perihal Surat', 'trim|required|max_length[255]');
		$fv->set_rules('recipient', 'Ditujukan Kepada', 'trim|max_length[200]');
		$fv->set_rules('sender_phone', 'No. WhatsApp / HP Narahubung', 'trim|required|max_length[30]');
		$fv->set_rules('sender_email', 'Email Narahubung', 'trim|valid_email|max_length[100]');
		$fv->set_rules('description', 'Ringkasan / Keterangan', 'trim');
		$fv->set_rules('agree', 'Pernyataan Keaslian', 'required', array('required' => 'Anda wajib menyetujui pernyataan keabsahan berkas surat.'));

		if ( ! $fv->run())
		{
			$this->show_form();
			return;
		}

		// 5. Berkas PDF / Gambar wajib diunggah
		if (empty($_FILES['letter_file']['name']))
		{
			$this->show_form(array('letter_file' => 'Berkas surat (PDF/Gambar resmi bertanda tangan & stempel) wajib dilampirkan.'));
			return;
		}

		$upload = $this->handle_upload('letter_file');
		if (isset($upload['error']))
		{
			$this->show_form(array('letter_file' => 'Gagal mengunggah berkas: '.$upload['error']));
			return;
		}

		// 6. Buat No. Agenda & Kode Resi Lacak
		$received_date = date('Y-m-d');
		$agenda_number = $this->Incoming_letter_model->generate_agenda_number($received_date);
		$tracking_code = $this->Incoming_letter_model->generate_tracking_code();

		$data = array(
			'agenda_number' => $agenda_number,
			'letter_number' => $this->input->post('letter_number', TRUE),
			'letter_date'   => $this->input->post('letter_date', TRUE),
			'received_date' => $received_date,
			'sender'        => $this->input->post('sender', TRUE),
			'recipient'     => $this->input->post('recipient', TRUE) ?: 'Kepala Sekolah',
			'subject'       => $this->input->post('subject', TRUE),
			'description'   => $this->input->post('description', TRUE),
			'file_path'     => isset($upload['path']) ? $upload['path'] : NULL,
			'file_name'     => isset($upload['name']) ? $upload['name'] : NULL,
			'status'        => 'baru',
			'is_external'   => 1,
			'tracking_code' => $tracking_code,
			'sender_phone'  => $this->input->post('sender_phone', TRUE),
			'sender_email'  => $this->input->post('sender_email', TRUE),
			'created_by'    => NULL,
		);

		$letter_id = $this->Incoming_letter_model->insert($data);

		if ($letter_id)
		{
			// Reset captcha
			$this->session->unset_userdata(array('ext_c_a', 'ext_c_b'));

			// Kirim notifikasi ke seluruh staf TU
			$this->load->model('Notification_model');
			$tu_users = $this->db->select('id')->from('users')->where_in('role', array('tu', 'superuser', 'admin'))->where('is_active', 1)->get()->result_array();
			foreach ($tu_users as $u)
			{
				send_notification(
					(int) $u['id'],
					'Surat Masuk Baru dari Instansi Luar',
					'Diterima surat online dari "'.$data['sender'].'" perihal: "'.$data['subject'].'" (No. Agenda: '.$agenda_number.'). Harap diverifikasi dan diteruskan ke Kepala Sekolah.',
					site_url('surat-masuk/detail/'.$letter_id),
					'info'
				);
			}

			// Simpan tracking_code di flash data untuk tampilan sukses
			$this->session->set_flashdata('new_ext_tracking', $tracking_code);
			redirect('kirim-surat/status/'.$tracking_code);
		}
		else
		{
			$this->show_form(array('general' => 'Terjadi kesalahan sistem saat menyimpan data surat. Silakan coba lagi.'));
		}
	}

	/** Cek status tanda terima surat oleh instansi pengirim. */
	public function status($code = '')
	{
		$raw_code = trim((string) ($code ?: $this->input->get('kode', TRUE)));
		$code = strtoupper(urldecode($raw_code));
		$letter = NULL;
		$dispositions = array();
		$searched = FALSE;

		// Jika input ternyata kode permohonan siswa (REQ-...), alihkan ke lacak permohonan siswa
		if (strpos($code, 'REQ-') === 0 || strpos($code, 'REQ') === 0)
		{
			$this->load->model('Letter_request_model');
			if ($this->Letter_request_model->find_by_code($code))
			{
				redirect('permohonan/cek?kode='.urlencode($code));
				return;
			}
		}

		if ($code !== '')
		{
			$searched = TRUE;
			$letter = $this->Incoming_letter_model->find_by_tracking($code);
			if ($letter)
			{
				$this->load->model('Disposition_model');
				$dispositions = $this->Disposition_model->for_incoming_letter((int) $letter['id']);
			}
		}

		$is_new = ($code !== '' && $letter && (string)$letter['tracking_code'] === (string) $this->session->flashdata('new_ext_tracking'));

		$this->public_view('public/external_status', array(
			'page_title'   => 'Tanda Terima & Pelacakan Surat Masuk Kedinasan',
			'code'         => $code,
			'letter'       => $letter,
			'dispositions' => $dispositions,
			'searched'     => $searched,
			'is_new'       => $is_new,
		));
	}

	protected function show_form(array $custom_errors = array())
	{
		$a = random_int(2, 9);
		$b = random_int(1, 9);
		$this->session->set_userdata(array('ext_c_a' => $a, 'ext_c_b' => $b));

		$this->public_view('public/external_form', array(
			'page_title'    => 'Kirim Surat Kedinasan / Instansi Luar',
			'captcha_q'     => $a.' + '.$b,
			'custom_errors' => $custom_errors,
		));
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

	protected function public_view($view, array $data = array())
	{
		$data['school']    = $this->school;
		$data['has_logo']  = (bool) $this->Settings_model->image_file('logo_path');
		$body = $this->load->view($view, $data, TRUE);
		$this->load->view('public/layout', array(
			'page_title'  => isset($data['page_title']) ? $data['page_title'] : 'Layanan Surat',
			'school'      => $this->school,
			'has_logo'    => $data['has_logo'],
			'content'     => $body,
		));
	}
}
