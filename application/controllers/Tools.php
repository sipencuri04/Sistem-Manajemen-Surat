<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Perintah CLI (HANYA dapat dijalankan dari terminal, tidak dari browser).
 *
 * Membuat akun administrator baru:
 *   php index.php tools create_admin <username> <password> "<Nama Lengkap>"
 *
 * Reset password administrator:
 *   php index.php tools reset_password <username> <password_baru>
 */
#[\AllowDynamicProperties]
class Tools extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if ( ! is_cli())
		{
			show_404();
		}
		$this->load->model('User_model');
	}

	public function index()
	{
		echo "Perintah tersedia:\n";
		echo "  php index.php tools create_admin <username> <password> \"<Nama Lengkap>\"\n";
		echo "  php index.php tools reset_password <username> <password_baru>\n";
	}

	public function create_admin($username = '', $password = '', $full_name = 'Administrator')
	{
		$full_name = urldecode($full_name);
		if ( ! preg_match('/^[A-Za-z0-9_\-]{4,50}$/', $username))
		{
			echo "Username harus 4-50 karakter (huruf, angka, _ atau -).\n";
			return;
		}
		if (strlen($password) < 8)
		{
			echo "Password minimal 8 karakter.\n";
			return;
		}
		if ($this->User_model->find_by_username($username))
		{
			echo "Username '$username' sudah ada. Gunakan reset_password.\n";
			return;
		}
		$id = $this->User_model->create($username, $password, $full_name);
		echo $id ? "Administrator '$username' berhasil dibuat (id $id).\n" : "Gagal membuat akun.\n";
	}

	public function reset_password($username = '', $password = '')
	{
		$user = $this->User_model->find_by_username($username);
		if ( ! $user)
		{
			echo "Username '$username' tidak ditemukan.\n";
			return;
		}
		if (strlen($password) < 8)
		{
			echo "Password minimal 8 karakter.\n";
			return;
		}
		$this->User_model->set_password($user['id'], $password);
		$this->db->where('id', $user['id'])->update('users', array('is_active' => 1));
		echo "Password '$username' berhasil diubah.\n";
	}

	public function test_email($to = '')
	{
		if ( ! filter_var($to, FILTER_VALIDATE_EMAIL))
		{
			echo "Penggunaan: php index.php tools test_email <alamat_tujuan@gmail.com>\n";
			return;
		}

		echo "Menguji koneksi SMTP dan pengiriman email ke: $to ...\n";
		$this->load->library('email');

		$from_email = getenv('SMTP_FROM_EMAIL') ?: (isset($_ENV['SMTP_FROM_EMAIL']) ? $_ENV['SMTP_FROM_EMAIL'] : 'noreply@sekolah.sch.id');
		$from_name  = getenv('SMTP_FROM_NAME') ?: (isset($_ENV['SMTP_FROM_NAME']) ? $_ENV['SMTP_FROM_NAME'] : 'Sistem Persuratan Sekolah');

		$this->email->from($from_email, $from_name);
		$this->email->to($to);
		$this->email->subject('Uji Coba SMTP Persuratan');
		$this->email->message('<div style="font-family:sans-serif; padding:15px; border:1px solid #3b82f6; border-radius:6px;"><h3>Koneksi Email Berhasil!</h3><p>Email uji coba ini dikirim dari Sistem Manajemen Persuratan Sekolah.</p></div>');

		if ($this->email->send())
		{
			echo "[SUKSES] Email uji coba berhasil dikirim ke $to!\n";
		}
		else
		{
			echo "[GAGAL] Pengiriman gagal. Keterangan error:\n";
			echo $this->email->print_debugger(array('headers', 'subject', 'body'));
		}
	}
}
