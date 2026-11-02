<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pengaturan identitas sekolah, kop, penandatangan, dan format penomoran.
 */
class Settings extends Admin_Controller
{
	/** Field gambar => [label, kunci media] */
	protected $images = array(
		'logo_path'       => array('Logo Sekolah (kiri)', 'logo'),
		'logo_right_path' => array('Logo Kanan (opsional)', 'logo-kanan'),
		'signature_path'  => array('Gambar Tanda Tangan Resmi', 'ttd'),
		'stamp_path'      => array('Gambar Stempel Resmi', 'stempel'),
	);

	public function __construct()
	{
		parent::__construct();
		$this->require_role(array('tu', 'admin'));
		$this->load->library('Letter_numbering');
		$this->load->model('Letter_type_model');
	}

	public function index()
	{
		if ($this->input->method(TRUE) === 'POST')
		{
			$this->save();
		}

		$s = $this->Settings_model->get(TRUE);
		$types = $this->Letter_type_model->all(TRUE);
		$example = $types ? $this->letter_numbering->format($s, $types[0], 1, date('Y-m-d')) : '';

		$this->render('settings/index', array(
			'page_title' => 'Pengaturan Sekolah',
			's'          => $s,
			'images'     => $this->images,
			'example'    => $example,
			'example_type' => $types ? $types[0] : NULL,
			'upload_errors' => isset($this->upload_errors) ? $this->upload_errors : array(),
		));
	}

	protected $upload_errors = array();

	protected function save()
	{
		$fv = $this->form_validation;
		$fv->set_rules('school_name', 'Nama sekolah', 'trim|required|max_length[200]');
		$fv->set_rules('kop_line1', 'Baris kop 1', 'trim|max_length[200]');
		$fv->set_rules('kop_line2', 'Baris kop 2', 'trim|max_length[200]');
		$fv->set_rules('school_code', 'Kode sekolah', 'trim|max_length[30]|regex_match[/^[A-Za-z0-9.\-]*$/]',
			array('regex_match' => '{field} hanya boleh huruf, angka, titik, dan tanda minus.'));
		$fv->set_rules('npsn', 'NPSN', 'trim|max_length[20]|alpha_numeric');
		$fv->set_rules('address', 'Alamat', 'trim|max_length[500]');
		$fv->set_rules('city', 'Kota/Kabupaten (tempat surat)', 'trim|required|max_length[100]');
		$fv->set_rules('postal_code', 'Kode pos', 'trim|max_length[10]|numeric');
		$fv->set_rules('phone', 'Telepon', 'trim|max_length[50]|regex_match[/^[0-9+()\-\s\/.,]*$/]',
			array('regex_match' => '{field} hanya boleh angka dan tanda + ( ) - /'));
		$fv->set_rules('email', 'Email', 'trim|max_length[150]|valid_email');
		$fv->set_rules('website', 'Website', 'trim|max_length[150]');
		$fv->set_rules('kop_template', 'Template kop', 'required|in_list[logo_kiri,logo_kiri_kanan,tanpa_logo]');
		$fv->set_rules('headmaster_name', 'Nama kepala sekolah', 'trim|required|max_length[150]');
		$fv->set_rules('headmaster_nip', 'NIP', 'trim|max_length[30]|regex_match[/^[0-9\s.\-]*$/]',
			array('regex_match' => '{field} hanya boleh berisi angka.'));
		$fv->set_rules('signer_title', 'Jabatan penandatangan', 'trim|required|max_length[150]');
		$fv->set_rules('number_format', 'Format nomor', 'trim|required|max_length[150]|callback__valid_format');
		$fv->set_rules('number_padding', 'Panjang nomor urut', 'required|integer|greater_than_equal_to[1]|less_than_equal_to[6]');
		$fv->set_rules('number_reset', 'Reset nomor', 'required|in_list[yearly,monthly,never]');
		$fv->set_rules('number_scope', 'Urutan nomor', 'required|in_list[global,per_type]');
		$fv->set_rules('letter_year', 'Tahun surat', 'trim|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]');
		$fv->set_rules('smtp_host', 'Host SMTP', 'trim|max_length[150]');
		$fv->set_rules('smtp_port', 'Port SMTP', 'trim|integer');
		$fv->set_rules('smtp_crypto', 'Enkripsi SMTP', 'in_list[tls,ssl,none]');
		$fv->set_rules('smtp_user', 'Email Akun Gmail/SMTP', 'trim|max_length[150]');
		$fv->set_rules('smtp_pass', 'Sandi Aplikasi / App Password', 'trim|max_length[255]');
		$fv->set_rules('smtp_from_name', 'Nama Pengirim Email', 'trim|max_length[150]');

		if ( ! $fv->run())
		{
			set_flash('danger', 'Pengaturan belum disimpan. Periksa kembali isian yang ditandai.');
			return;
		}

		$current = $this->Settings_model->get(TRUE);

		$data = array();
		foreach ($this->Settings_model->editable as $key)
		{
			$data[$key] = trim((string) $this->input->post($key));
		}
		$data['show_nip']       = $this->input->post('show_nip') ? 1 : 0;
		$data['smtp_enabled']   = $this->input->post('smtp_enabled') ? 1 : 0;
		$data['smtp_port']      = !empty($data['smtp_port']) ? (int) $data['smtp_port'] : 587;
		$data['number_padding'] = (int) $data['number_padding'];
		$data['letter_year']    = $data['letter_year'] !== '' ? (int) $data['letter_year'] : NULL;

		// Jika password dikosongkan saat simpan, pertahankan password lama
		if ($data['smtp_pass'] === '' && ! empty($current['smtp_pass']))
		{
			$data['smtp_pass'] = $current['smtp_pass'];
		}

		// Upload gambar (opsional)
		$old_files = array();
		foreach ($this->images as $field => $meta)
		{
			if (empty($_FILES[$field]['name']))
			{
				continue;
			}
			$result = $this->handle_upload($field);
			if (is_array($result))
			{
				$this->upload_errors[$field] = $meta[0].': '.$result['error'];
				continue;
			}
			$data[$field] = $result;
			if ( ! empty($current[$field]))
			{
				$old_files[] = $current[$field];
			}
		}

		if ( ! empty($this->upload_errors))
		{
			// Hapus file yang baru terunggah agar tidak menjadi sampah.
			foreach ($this->images as $field => $meta)
			{
				if (isset($data[$field])) @unlink(storage_path($data[$field]));
			}
			set_flash('danger', 'Pengaturan belum disimpan: '.implode(' ', $this->upload_errors));
			return;
		}

		if ($this->Settings_model->update($data))
		{
			foreach ($old_files as $old)
			{
				$path = storage_path($old);
				if (is_file($path)) @unlink($path);
			}
			$this->school = $this->Settings_model->get(TRUE);

			if ($this->input->post('save_email_config'))
			{
				$status_txt = !empty($data['smtp_enabled']) ? 'Aktif' : 'Nonaktif';
				set_flash('success', '<strong>Konfigurasi Email Berhasil Disimpan!</strong> Kredensial Gmail TU (<strong>' . htmlspecialchars($data['smtp_user'] ?: '-') . '</strong>) kini berstatus <strong>' . $status_txt . '</strong>. Silakan klik tombol <u>Uji Coba Kirim Email</u> untuk memverifikasi koneksi.', TRUE);
				redirect('pengaturan#email_section');
				return;
			}

			set_flash('success', 'Pengaturan sekolah berhasil disimpan. Perubahan langsung dipakai pada PDF surat.');
			redirect('pengaturan');
		}
		set_flash('danger', 'Pengaturan gagal disimpan.');
	}

	/**
	 * Unggah satu gambar (PNG/JPG, maks 5 MB) ke storage/uploads.
	 * @return string|array path relatif atau ['error' => pesan]
	 */
	protected function handle_upload($field)
	{
		$dir = storage_path('uploads');
		if ( ! is_dir($dir)) @mkdir($dir, 0755, TRUE);

		$this->load->library('upload');
		$this->upload->initialize(array(
			'upload_path'      => $dir,
			'allowed_types'    => 'png|jpg|jpeg',
			'max_size'         => 5120,
			'max_width'        => 4000,
			'max_height'       => 4000,
			'encrypt_name'     => TRUE,       // nama file acak & aman
			'file_ext_tolower' => TRUE,
			'remove_spaces'    => TRUE,
			'detect_mime'      => TRUE,
		), TRUE);

		if ( ! $this->upload->do_upload($field))
		{
			return array('error' => strip_tags($this->upload->display_errors('', '')));
		}
		$up = $this->upload->data();

		// Verifikasi ulang bahwa file benar-benar gambar PNG/JPEG.
		$info = @getimagesize($up['full_path']);
		if ( ! $info OR ! in_array($info['mime'], array('image/png', 'image/jpeg'), TRUE))
		{
			@unlink($up['full_path']);
			return array('error' => 'File bukan gambar PNG/JPG yang valid.');
		}
		return 'uploads/'.$up['file_name'];
	}

	/** Hapus salah satu gambar pengaturan. */
	public function remove_image($field = '')
	{
		$this->require_post();
		if ( ! isset($this->images[$field]))
		{
			show_404();
		}
		$s = $this->Settings_model->get(TRUE);
		if ( ! empty($s[$field]))
		{
			$path = storage_path($s[$field]);
			if (is_file($path)) @unlink($path);
			$this->Settings_model->update(array($field => NULL));
		}
		set_flash('success', $this->images[$field][0].' dihapus.');
		redirect('pengaturan');
	}

	public function _valid_format($format)
	{
		$check = $this->letter_numbering->validate_format((string) $format);
		if ($check !== TRUE)
		{
			$this->form_validation->set_message('_valid_format', $check);
			return FALSE;
		}
		return TRUE;
	}

	/** Uji coba kirim email lewat antarmuka web. */
	public function test_email()
	{
		$this->require_post();
		$to = trim((string) $this->input->post('test_recipient', TRUE));

		if ( ! filter_var($to, FILTER_VALIDATE_EMAIL))
		{
			set_flash('danger', '<strong>Alamat Email Tidak Valid!</strong> Silakan masukkan alamat email tujuan yang benar (contoh: nama@gmail.com).', TRUE);
			redirect('pengaturan#email_section');
			return;
		}

		$s = $this->Settings_model->get(TRUE);

		// Prioritaskan kredensial langsung dari modal jika user menguji sebelum simpan
		$smtp_user   = trim((string) $this->input->post('test_smtp_user', TRUE));
		if ($smtp_user === '') {
			$smtp_user = !empty($s['smtp_user']) ? $s['smtp_user'] : (getenv('SMTP_USER') ?: (isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : ''));
		}

		$smtp_pass   = trim((string) $this->input->post('test_smtp_pass', TRUE));
		if ($smtp_pass === '') {
			$smtp_pass = !empty($s['smtp_pass']) ? $s['smtp_pass'] : (getenv('SMTP_PASS') ?: (isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : ''));
		}

		$smtp_host   = trim((string) $this->input->post('test_smtp_host', TRUE));
		if ($smtp_host === '') {
			$smtp_host = !empty($s['smtp_host']) ? $s['smtp_host'] : (getenv('SMTP_HOST') ?: (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : 'smtp.gmail.com'));
		}

		$smtp_port   = (int) $this->input->post('test_smtp_port', TRUE);
		if ($smtp_port <= 0) {
			$smtp_port = !empty($s['smtp_port']) ? (int) $s['smtp_port'] : 587;
		}

		$smtp_crypto = trim((string) $this->input->post('test_smtp_crypto', TRUE));
		if ($smtp_crypto === '') {
			$smtp_crypto = !empty($s['smtp_crypto']) ? $s['smtp_crypto'] : 'tls';
		}
		if ($smtp_crypto === 'none') $smtp_crypto = '';

		$from_email  = !empty($smtp_user) ? $smtp_user : 'noreply@smk.sch.id';
		$from_name   = !empty($s['smtp_from_name']) ? $s['smtp_from_name'] : (!empty($s['school_name']) ? $s['school_name'] : 'SMK Muhammadiyah Bandongan');

		if (empty($smtp_user) || empty($smtp_pass))
		{
			set_flash('danger', '<strong>Gagal Uji Coba:</strong> Alamat Gmail TU dan Sandi Aplikasi Google (App Password) belum terisi. Silakan isi form lalu klik <strong>Simpan Konfigurasi Email</strong> terlebih dahulu.', TRUE);
			redirect('pengaturan#email_section');
			return;
		}

		$config = array(
			'protocol'    => !empty($smtp_host) ? 'smtp' : 'mail',
			'smtp_host'   => $smtp_host,
			'smtp_port'   => $smtp_port,
			'smtp_user'   => $smtp_user,
			'smtp_pass'   => $smtp_pass,
			'smtp_crypto' => $smtp_crypto,
			'mailtype'    => 'html',
			'charset'     => 'utf-8',
			'newline'     => "\r\n",
			'crlf'        => "\r\n",
			'wordwrap'    => TRUE,
		);

		$this->load->library('email');
		$this->email->initialize($config);
		$this->email->from($from_email, $from_name);
		$this->email->to($to);
		$this->email->subject('[' . $from_name . '] Uji Coba Koneksi Gmail / SMTP Berhasil');
		$this->load->library('email_theme');
		$body = $this->email_theme->render(array(
			'title'          => 'Uji Coba Koneksi Gmail / SMTP Berhasil',
			'recipient_name' => 'Administrator Persuratan',
			'badge_type'     => 'success',
			'badge_text'     => 'KONEKSI GMAIL RESMI TERHUBUNG 100%',
			'message'        => 'Selamat! Pengaturan Gmail SMTP pada <strong>' . htmlspecialchars($from_name) . '</strong> telah berhasil diverifikasi dan siap digunakan untuk pengiriman notifikasi otomatis.',
			'details'        => array(
				'Alamat Gmail TU' => htmlspecialchars($from_email),
				'Penerima Uji Coba' => htmlspecialchars($to),
				'Host SMTP' => htmlspecialchars($smtp_host) . ':' . $smtp_port . ' (' . strtoupper($smtp_crypto ?: 'PLAIN') . ')',
				'Status Protokol' => '<span style="color:#15803d; font-weight:700;">Aktif &amp; Terenkripsi</span>',
				'Waktu Uji Coba' => date('d/m/Y H:i:s') . ' WIB',
			),
			'button_url'     => site_url('pengaturan#email_section'),
			'button_text'    => 'Kelola Pengaturan Sistem',
			'closing_note'   => 'Sistem Manajemen Persuratan kini siap secara penuh mengirimkan notifikasi surat masuk, disposisi pimpinan, dan berkas surat resmi kepada siswa secara real-time.',
		));
		$this->email->message($body);

		if ($this->email->send())
		{
			set_flash('success', '<strong>Koneksi Gmail Berhasil 100%!</strong> Email uji coba berhasil dikirim ke <u>' . htmlspecialchars($to) . '</u> dari <u>' . htmlspecialchars($from_email) . '</u>. Silakan periksa Kotak Masuk (Inbox) atau folder Spam akun Anda.', TRUE);
		}
		else
		{
			$raw_debug = $this->email->print_debugger(array('headers', 'subject', 'body'));
			$clean_debug = trim(strip_tags($raw_debug));

			// Analisis diagnosa penyebab gagal
			$diagnosis = 'Terjadi kendala saat menghubungi server Google SMTP.';
			$solusi = 'Pastikan server memiliki koneksi internet keluar (outbound connection).';

			if (stripos($clean_debug, '535') !== FALSE || stripos($clean_debug, 'Username and Password not accepted') !== FALSE || stripos($clean_debug, 'BadCredentials') !== FALSE)
			{
				$diagnosis = '<strong>Autentikasi Gagal (Kode 535):</strong> Sandi Aplikasi Google (App Password) atau Alamat Gmail salah / ditolak.';
				$solusi = 'Pastikan menggunakan 16 karakter Sandi Aplikasi dari Akun Google (<a href="https://myaccount.google.com/apppasswords" target="_blank" class="alert-link">myaccount.google.com/apppasswords</a>) tanpa spasi. Bukan password login biasa.';
			}
			elseif (stripos($clean_debug, 'timed out') !== FALSE || stripos($clean_debug, 'Connection refused') !== FALSE || stripos($clean_debug, 'fsockopen') !== FALSE)
			{
				$diagnosis = '<strong>Koneksi Timeout / Terblokir:</strong> Server tidak dapat terhubung ke ' . htmlspecialchars($smtp_host) . ':' . $smtp_port . '.';
				$solusi = 'Port ' . $smtp_port . ' mungkin dibatasi oleh firewall provider hosting. Coba buka "Pengaturan SMTP Lanjutan", ganti Port ke <strong>465</strong> dan Protokol ke <strong>SSL</strong>, lalu simpan.';
			}
			elseif (stripos($clean_debug, 'from') !== FALSE || stripos($clean_debug, 'sender') !== FALSE)
			{
				$diagnosis = '<strong>Alamat Pengirim Ditolak:</strong> Google menolak alamat email pengirim.';
				$solusi = 'Pastikan "Alamat Gmail TU" sama persis dengan akun Google pemilik Sandi Aplikasi.';
			}

			set_flash('danger', '<strong>Uji Coba Gagal Dikirim!</strong><br>' . $diagnosis . '<br><span class="small">' . $solusi . '</span><details class="mt-2"><summary class="small text-muted" style="cursor:pointer;"><i class="fa-solid fa-code me-1"></i>Lihat Rincian Teknis Error (Debug Log)</summary><pre class="p-2 bg-dark text-light rounded mt-1 text-wrap" style="font-size:11px; max-height:160px; overflow-y:auto;">' . htmlspecialchars($clean_debug) . '</pre></details>', TRUE);
		}

		redirect('pengaturan#email_section');
	}
}
