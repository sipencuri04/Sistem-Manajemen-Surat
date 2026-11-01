<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Portal publik permohonan surat oleh siswa (TANPA LOGIN).
 *
 * Siswa memilih jenis surat yang diizinkan, mengisi data sendiri, lalu
 * mendapat kode lacak. Surat baru diterbitkan (dan diberi nomor) setelah
 * diperiksa TU melalui menu "Permohonan Siswa".
 *
 * Perlindungan karena tanpa login: CSRF, honeypot, soal hitungan sederhana,
 * batas jumlah permohonan per IP dan per nomor HP, validasi server penuh.
 */
class Permohonan extends MY_Controller
{
	const MAX_PER_HOUR_IP   = 5;
	const MAX_PER_DAY_IP    = 15;
	const MAX_PENDING_PHONE = 3;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Letter_type_model', 'Letter_request_model'));
		$this->load->library('Template_registry');
		$this->output->set_header('X-Frame-Options: DENY');
		$this->output->set_header('X-Content-Type-Options: nosniff');
	}

	/** Daftar jenis surat yang boleh diajukan siswa. */
	public function index()
	{
		$types = array();
		foreach ($this->Letter_type_model->all_public() as $t)
		{
			$tpl = $this->template_registry->get($t['template_key']);
			if ($tpl && $this->template_registry->exists($t['template_key']))
			{
				$t['guide'] = $tpl['guide'];
				$types[] = $t;
			}
		}
		$this->public_view('public/index', array('page_title' => 'Permohonan Surat', 'types' => $types));
	}

	/** Formulir permohonan. */
	public function form($type_id = 0)
	{
		list($type, $tpl) = $this->load_public_type($type_id);
		$this->show_form($type, $tpl, array(), array());
	}

	/** Simpan permohonan. */
	public function kirim($type_id = 0)
	{
		$this->require_post();
		list($type, $tpl) = $this->load_public_type($type_id);
		$ip = $this->input->ip_address();

		// 1. Honeypot: kolom tersembunyi yang hanya diisi robot.
		if (trim((string) $this->input->post('website')) !== '')
		{
			log_message('error', 'Permohonan ditolak (honeypot) dari '.$ip);
			show_error('Permohonan tidak dapat diproses.', 400);
		}

		// 2. Batas jumlah permohonan per IP.
		$hour = $this->Letter_request_model->count_recent_by_ip($ip, date('Y-m-d H:i:s', time() - 3600));
		$day  = $this->Letter_request_model->count_recent_by_ip($ip, date('Y-m-d H:i:s', time() - 86400));
		if ($hour >= self::MAX_PER_HOUR_IP OR $day >= self::MAX_PER_DAY_IP)
		{
			log_message('error', 'Permohonan dibatasi (rate limit) dari '.$ip);
			$this->show_form($type, $tpl, $this->input->post('f'), array(),
				'Terlalu banyak permohonan dari perangkat/jaringan ini. Silakan coba lagi nanti atau datang langsung ke ruang TU.');
			return;
		}

		// 3. Validasi isian.
		$fv = $this->form_validation;
		$fv->set_rules('requester_phone', 'Nomor WhatsApp/HP', 'trim|required|max_length[20]|callback__valid_phone');
		$fv->set_rules('requester_note', 'Catatan untuk TU', 'trim|max_length[500]');
		$fv->set_rules('captcha', 'Jawaban hitungan', 'trim|required|callback__check_captcha');
		$fv->set_rules('agree', 'Pernyataan kebenaran data', 'required',
			array('required' => 'Centang pernyataan bahwa data yang diisi benar.'));
		$need_name = ! ($tpl['recipient_field'] && isset($tpl['fields'][$tpl['recipient_field']]));
		if ($need_name)
		{
			$fv->set_rules('requester_name', 'Nama pemohon', 'trim|required|max_length[150]');
		}
		$valid = $fv->run();
		list($data, $f_errors) = $this->template_registry->collect($tpl, $this->input->post('f'));
		$valid = $valid && empty($f_errors);

		$phone = $this->normalize_phone((string) $this->input->post('requester_phone'));
		if ($valid && $this->Letter_request_model->count_pending_by_phone($phone) >= self::MAX_PENDING_PHONE)
		{
			$this->show_form($type, $tpl, $this->input->post('f'), $f_errors,
				'Nomor HP ini masih memiliki '.self::MAX_PENDING_PHONE.' permohonan yang belum diproses. Tunggu hingga diproses TU.');
			return;
		}
		if ( ! $valid)
		{
			$this->show_form($type, $tpl, $this->input->post('f'), $f_errors);
			return;
		}

		$name  = $need_name ? trim($this->input->post('requester_name')) : (string) $data[$tpl['recipient_field']];
		$class = isset($data['kelas']) ? (string) $data['kelas'] : (isset($data['jabatan_kelas']) ? (string) $data['jabatan_kelas'] : '');

		try
		{
			$code = $this->Letter_request_model->generate_code();
			$id = $this->Letter_request_model->insert(array(
				'tracking_code'   => $code,
				'letter_type_id'  => (int) $type['id'],
				'requester_name'  => mb_substr($name, 0, 150),
				'requester_class' => mb_substr($class, 0, 50),
				'requester_phone' => $phone,
				'content_data'    => json_encode($data, JSON_UNESCAPED_UNICODE),
				'requester_note'  => trim((string) $this->input->post('requester_note')) ?: NULL,
				'status'          => 'pending',
				'ip_address'      => $ip,
			));
			if ( ! $id) throw new RuntimeException('insert gagal');
		}
		catch (Exception $ex)
		{
			log_message('error', 'Simpan permohonan gagal: '.$ex->getMessage());
			$this->show_form($type, $tpl, $this->input->post('f'), array(), 'Permohonan gagal dikirim. Silakan coba lagi.');
			return;
		}

		$this->session->unset_userdata('captcha_answer');
		$this->session->set_flashdata('new_request', $code);
		redirect('permohonan/cek?kode='.$code);
	}

	/** Cek status permohonan dengan kode lacak. */
	public function cek()
	{
		$code = (string) $this->input->get('kode', TRUE);
		$request = $code !== '' ? $this->Letter_request_model->find_by_code($code) : NULL;
		$this->public_view('public/status', array(
			'page_title' => 'Cek Status Permohonan',
			'code'       => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $code), 0, 10)),
			'request'    => $request,
			'searched'   => $code !== '',
			'is_new'     => $this->session->flashdata('new_request') === ($request ? $request['tracking_code'] : '#'),
		));
	}

	/**
	 * Unduh atau cetak surat PDF untuk siswa berdasarkan kode permohonan.
	 * $mode: 'unduh' (download attachment) atau 'cetak' (inline di browser agar bisa langsung dicetak).
	 */
	public function dokumen($code = '', $mode = 'cetak')
	{
		$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $code));
		$request = $this->Letter_request_model->find_by_code($code);
		if ( ! $request OR ! in_array($request['status'], array('approved', 'taken'), TRUE) OR empty($request['letter_id']))
		{
			show_error('Dokumen surat belum tersedia untuk diunduh/dicetak atau permohonan belum selesai diproses.', 404, 'Dokumen Belum Tersedia');
		}

		$this->load->model('Letter_model');
		$letter = $this->Letter_model->find((int) $request['letter_id']);
		if ( ! $letter)
		{
			show_error('Data surat tidak ditemukan.', 404, 'Surat Tidak Ditemukan');
		}

		$type = $this->Letter_type_model->find_with_template((int) $letter['letter_type_id']);
		if ( ! $type)
		{
			show_error('Jenis surat tidak ditemukan.', 404, 'Jenis Surat Tidak Ditemukan');
		}

		$this->load->library('Letter_renderer');
		try
		{
			$html = $this->letter_renderer->html($letter, $type, $this->school, 'pdf');
			$filename = safe_filename(($letter['type_code'] ?: 'SURAT').'_'.$letter['letter_number']);
			$this->letter_renderer->stream($html, $filename, $mode === 'unduh');
		}
		catch (Exception $ex)
		{
			log_message('error', 'Gagal memproses PDF permohonan '.$code.': '.$ex->getMessage());
			show_error('PDF gagal diproses. Silakan hubungi ruang Tata Usaha.', 500, 'Kesalahan PDF');
		}
	}

	/* ------------------------------------------------------------------ */

	protected function load_public_type($type_id)
	{
		$type = $this->Letter_type_model->find((int) $type_id);
		if ( ! $type OR ! (int) $type['is_active'] OR ! (int) $type['allow_public'])
		{
			show_error('Jenis surat ini tidak dapat diajukan secara online. Silakan hubungi ruang TU.', 404, 'Tidak Tersedia');
		}
		$tpl = $this->template_registry->get($type['template_key']);
		if ( ! $tpl)
		{
			show_404();
		}
		return array($type, $tpl);
	}

	protected function show_form(array $type, array $tpl, $f, array $f_errors, $error = NULL)
	{
		// Soal hitungan sederhana sebagai penyaring robot.
		$a = random_int(2, 9);
		$b = random_int(1, 9);
		$this->session->set_userdata('captcha_answer', $a + $b);

		$this->public_view('public/form', array(
			'page_title' => 'Ajukan '.$type['name'],
			'type'       => $type,
			'tpl'        => $tpl,
			'f'          => is_array($f) ? $f : array(),
			'f_errors'   => $f_errors,
			'error'      => $error,
			'captcha_q'  => $a.' + '.$b,
			'need_name'  => ! ($tpl['recipient_field'] && isset($tpl['fields'][$tpl['recipient_field']])),
		));
	}

	protected function public_view($view, array $data)
	{
		$data['school']   = $this->school;
		$data['has_logo'] = (bool) $this->Settings_model->image_file('logo_path');
		$data['content']  = $this->load->view($view, $data, TRUE);
		$this->load->view('public/layout', $data);
	}

	protected function normalize_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', $phone);
		if (strpos($digits, '62') === 0)
		{
			$digits = '0'.substr($digits, 2);
		}
		return $digits;
	}

	public function _valid_phone($phone)
	{
		if ( ! preg_match('/^08[0-9]{7,12}$/', $this->normalize_phone((string) $phone)))
		{
			$this->form_validation->set_message('_valid_phone', '{field} tidak valid. Contoh: 081234567890.');
			return FALSE;
		}
		return TRUE;
	}

	public function _check_captcha($answer)
	{
		$expected = $this->session->userdata('captcha_answer');
		if ($expected === NULL OR (int) $answer !== (int) $expected OR ! ctype_digit((string) $answer))
		{
			$this->form_validation->set_message('_check_captcha', 'Jawaban hitungan salah. Coba lagi.');
			return FALSE;
		}
		return TRUE;
	}
}
