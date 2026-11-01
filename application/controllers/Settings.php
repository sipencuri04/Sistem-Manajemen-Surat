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
			set_flash('danger', 'Alamat email tujuan uji coba tidak valid.');
			redirect('pengaturan#email_section');
			return;
		}

		$s = $this->Settings_model->get(TRUE);
		$smtp_host   = !empty($s['smtp_host']) ? $s['smtp_host'] : (getenv('SMTP_HOST') ?: (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : ''));
		$smtp_user   = !empty($s['smtp_user']) ? $s['smtp_user'] : (getenv('SMTP_USER') ?: (isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : ''));
		$smtp_pass   = !empty($s['smtp_pass']) ? $s['smtp_pass'] : (getenv('SMTP_PASS') ?: (isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : ''));
		$smtp_port   = !empty($s['smtp_port']) ? (int) $s['smtp_port'] : 587;
		$smtp_crypto = !empty($s['smtp_crypto']) ? $s['smtp_crypto'] : 'tls';
		if ($smtp_crypto === 'none') $smtp_crypto = '';

		$from_email  = !empty($s['smtp_from_email']) ? $s['smtp_from_email'] : (!empty($smtp_user) ? $smtp_user : 'noreply@sekolah.sch.id');
		$from_name   = !empty($s['smtp_from_name']) ? $s['smtp_from_name'] : (!empty($s['school_name']) ? $s['school_name'] : 'Sistem Persuratan');

		if (empty($smtp_user) || empty($smtp_pass))
		{
			set_flash('danger', 'Gagal: Email pengirim (Gmail TU) dan Sandi Aplikasi Google belum diisi. Simpan pengaturan terlebih dahulu.');
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
		$this->email->subject('[' . $from_name . '] Uji Coba Koneksi Gmail / SMTP');
		$this->email->message('<div style="font-family:sans-serif; max-width:550px; padding:20px; border:1px solid #3b82f6; border-radius:8px; background:#f8fafc;"><h3 style="color:#1e3a8a; margin-top:0;">Koneksi Email Berhasil!</h3><p style="color:#334155;">Selamat! Pengaturan Gmail / SMTP pada Sistem Persuratan Sekolah telah berhasil terhubung dan siap mengirimkan notifikasi ke guru, staf, maupun Kepala Sekolah.</p><hr style="border:none; border-top:1px solid #e2e8f0; margin:15px 0;"><small style="color:#64748b;">Dikirim dari: ' . htmlspecialchars($from_email) . ' &middot; ' . date('d/m/Y H:i:s') . '</small></div>');

		if ($this->email->send())
		{
			set_flash('success', 'Email uji coba berhasil dikirim ke ' . htmlspecialchars($to) . '! Silakan periksa kotak masuk Gmail Anda.');
		}
		else
		{
			$debug_msg = $this->email->print_debugger(array('headers', 'subject', 'body'));
			set_flash('danger', 'Gagal mengirim email uji coba. Keterangan error: ' . strip_tags($debug_msg));
		}

		redirect('pengaturan#email_section');
	}
}
