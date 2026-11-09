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
		echo "  php index.php tools test_email <alamat_tujuan@gmail.com>\n";
		echo "  php index.php tools seed_dummy\n";
		echo "  php index.php tools seed_test_users\n";
		echo "  php index.php tools reset_and_seed\n";
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

	/**
	 * Membuat 2 data dummy yang belum diproses TU:
	 * 1. Surat dari sistem (permohonan mandiri siswa status 'pending')
	 * 2. Surat dari instansi luar (surat masuk status 'baru')
	 */
	public function seed_dummy()
	{
		$this->load->model(array('Letter_request_model', 'Incoming_letter_model'));

		// 1. Data Dummy Surat dari Sistem (Permohonan Siswa)
		$code = $this->Letter_request_model->generate_code();
		$req_data = array(
			'tracking_code'   => $code,
			'letter_type_id'  => 1, // SKA - Surat Keterangan Siswa Aktif
			'requester_name'  => 'Noxa Aulia Pratama',
			'requester_class' => 'XII TKJ 1',
			'requester_phone' => '+62 877-4177-7837',
			'requester_email' => 'noxapremia02@gmail.com',
			'content_data'    => json_encode(array(
				'nama'            => 'Noxa Aulia Pratama',
				'nis'             => '10895',
				'nisn'            => '0068492088',
				'tempat_lahir'    => 'Magelang',
				'tanggal_lahir'   => '2008-04-18',
				'kelas'           => 'XII TKJ 1',
				'jurusan'         => 'Teknik Komputer dan Jaringan',
				'nama_orang_tua'  => 'Bambang Premia S.',
				'keperluan'       => 'Persyaratan Pengajuan Beasiswa Pendidikan Prestasi & Bantuan Kuliah Tahun 2026',
				'tahun_pelajaran' => '2025/2026',
			), JSON_UNESCAPED_UNICODE),
			'requester_note'  => 'Mohon bantuannya Bapak/Ibu TU untuk diproses surat keterangan aktif ini, berkas beasiswa harus segera saya serahkan. Terima kasih.',
			'status'          => 'pending',
			'ip_address'      => '127.0.0.1',
		);
		$req_id = $this->Letter_request_model->insert($req_data);
		echo "[1/2] Data Dummy Permohonan Siswa (Sistem) berhasil dibuat:\n";
		echo "      - ID: $req_id\n";
		echo "      - Kode Lacak: $code\n";
		echo "      - Pemohon: Noxa Aulia Pratama (XII TKJ 1)\n";
		echo "      - No. HP/WA: +62 877-4177-7837\n";
		echo "      - Email: noxapremia02@gmail.com\n";
		echo "      - Status: pending (Menunggu Diproses TU di menu Permohonan Siswa)\n\n";

		// 2. Data Dummy Surat Masuk dari Instansi
		$agenda = $this->Incoming_letter_model->generate_agenda_number();
		$tracking = $this->Incoming_letter_model->generate_tracking_code();
		$inc_data = array(
			'agenda_number' => $agenda,
			'letter_number' => '421.3/1084/Disdikbud.V/2026',
			'letter_date'   => date('Y-m-d', strtotime('-1 day')),
			'received_date' => date('Y-m-d'),
			'sender'        => 'Dinas Pendidikan dan Kebudayaan Cabang Wilayah V Prov. Jawa Tengah',
			'recipient'     => 'Kepala SMK Muhammadiyah Bandongan',
			'subject'       => 'Undangan Rapat Koordinasi dan Verifikasi Tempat Uji Kompetensi Keahlian (TUK) SMK Tahun 2026',
			'description'   => 'Mengharap kehadiran Kepala Sekolah dan 1 (satu) orang Ketua Kompetensi Keahlian TKJ/TKR pada hari Kamis, pukul 08.30 WIB di Aula Balai Pengendali Pendidikan Menengah perihal Verifikasi Kelayakan TUK UKK Tahun 2026.',
			'status'        => 'baru',
			'is_external'   => 1,
			'tracking_code' => $tracking,
			'sender_phone'  => '+62 877-4177-7837',
			'sender_email'  => 'noxapremia02@gmail.com',
			'created_by'    => 4, // ID staf TU Siti Rahmawati
		);
		$inc_id = $this->Incoming_letter_model->insert($inc_data);
		echo "[2/2] Data Dummy Surat Masuk dari Instansi berhasil dibuat:\n";
		echo "      - ID: $inc_id\n";
		echo "      - No. Agenda: $agenda\n";
		echo "      - Pengirim: Dinas Pendidikan dan Kebudayaan Cabang Wilayah V Prov. Jawa Tengah\n";
		echo "      - Perihal: Undangan Rapat Koordinasi dan Verifikasi TUK UKK SMK 2026\n";
		echo "      - Status: baru (Menunggu Tindak Lanjut TU di menu Surat Masuk)\n";
	}

	/**
	 * Membuat 4 akun pengujian multi-role dengan no WA dan email yang sama
	 * untuk verifikasi alur persuratan & pengesahan tanda tangan + stempel.
	 */
	public function seed_test_users()
	{
		$phone = '+62 877-4177-7837';
		$email = 'noxapremia02@gmail.com';
		$password = 'testing123';
		$pass_hash = password_hash($password, PASSWORD_BCRYPT);

		$test_users = array(
			array(
				'username'  => 'test_kepsek',
				'full_name' => 'Drs. H. Mulyadi Pratama, M.Pd',
				'role'      => 'kepala_sekolah',
				'position'  => 'Kepala Sekolah',
				'nip'       => '19720315 199802 1 003',
			),
			array(
				'username'  => 'test_tu',
				'full_name' => 'Noxa Anindya, S.AP',
				'role'      => 'tu',
				'position'  => 'Staf Administrasi & Persuratan TU',
				'nip'       => '19890520 201403 2 006',
			),
			array(
				'username'  => 'test_pegawai',
				'full_name' => 'Premia Bagus Saputra, S.Kom',
				'role'      => 'pegawai',
				'position'  => 'Guru Produktif / Ka. Bengkel TKJ',
				'nip'       => '19920810 201902 1 007',
			),
			array(
				'username'  => 'test_admin',
				'full_name' => 'Administrator Penguji Sistem',
				'role'      => 'superuser',
				'position'  => 'Superuser Administrator',
				'nip'       => '19850101 201001 1 001',
			),
		);

		echo "Menyiapkan akun testing (Password default: $password)...\n\n";

		foreach ($test_users as $u)
		{
			$existing = $this->db->get_where('users', array('username' => $u['username']))->row_array();
			if ($existing)
			{
				$this->db->where('id', $existing['id'])->update('users', array(
					'password_hash' => $pass_hash,
					'full_name'     => $u['full_name'],
					'nip'           => $u['nip'],
					'position'      => $u['position'],
					'phone'         => $phone,
					'email'         => $email,
					'role'          => $u['role'],
					'is_active'     => 1,
					'failed_attempts' => 0,
					'locked_until'  => NULL,
					'updated_at'    => date('Y-m-d H:i:s'),
				));
				echo "[UPDATE] Akun '{$u['username']}' diperbarui: {$u['full_name']} ({$u['role']})\n";
			}
			else
			{
				$this->db->insert('users', array(
					'username'      => $u['username'],
					'password_hash' => $pass_hash,
					'full_name'     => $u['full_name'],
					'nip'           => $u['nip'],
					'position'      => $u['position'],
					'phone'         => $phone,
					'email'         => $email,
					'role'          => $u['role'],
					'is_active'     => 1,
					'created_at'    => date('Y-m-d H:i:s'),
				));
				$new_id = $this->db->insert_id();
				echo "[BUAT] Akun '{$u['username']}' berhasil dibuat (ID $new_id): {$u['full_name']} ({$u['role']})\n";
			}
		}

		echo "\nSemua akun testing siap digunakan dengan password: $password\n";
	}

	/**
	 * Membersihkan data surat lama dan memasukkan 10 data dummy baru
	 * (5 surat masuk instansi dan 5 permohonan surat siswa).
	 */
	public function reset_and_seed()
	{
		echo "Membersihkan data surat lama...\n";
		$this->db->query("SET FOREIGN_KEY_CHECKS = 0");
		$this->db->query("DELETE FROM `letter_files`");
		$this->db->query("DELETE FROM `letter_counters`");
		$this->db->query("DELETE FROM `disposition_activities`");
		$this->db->query("DELETE FROM `disposition_recipients`");
		$this->db->query("DELETE FROM `dispositions`");
		$this->db->query("DELETE FROM `letter_requests`");
		$this->db->query("DELETE FROM `incoming_letters`");
		$this->db->query("DELETE FROM `letters`");
		$this->db->query("DELETE FROM `queue_tickets`");
		$this->db->query("DELETE FROM `notifications` WHERE `type` IN ('surat_masuk', 'persetujuan', 'disposisi', 'permohonan')");
		$this->db->query("DELETE FROM `audit_logs`");
		$this->db->query("ALTER TABLE `letter_files` AUTO_INCREMENT = 1");
		$this->db->query("ALTER TABLE `disposition_activities` AUTO_INCREMENT = 1");
		$this->db->query("ALTER TABLE `disposition_recipients` AUTO_INCREMENT = 1");
		$this->db->query("ALTER TABLE `dispositions` AUTO_INCREMENT = 1");
		$this->db->query("ALTER TABLE `letter_requests` AUTO_INCREMENT = 1");
		$this->db->query("ALTER TABLE `incoming_letters` AUTO_INCREMENT = 1");
		$this->db->query("ALTER TABLE `letters` AUTO_INCREMENT = 1");
		$this->db->query("ALTER TABLE `queue_tickets` AUTO_INCREMENT = 1");
		$this->db->query("ALTER TABLE `audit_logs` AUTO_INCREMENT = 1");
		$this->db->query("SET FOREIGN_KEY_CHECKS = 1");
		echo "[BERSIH] Semua surat lama, disposisi, dan berkas berhasil direset.\n\n";

		$sql_file = FCPATH.'database/reset_and_seed_10_letters.sql';
		if (is_file($sql_file))
		{
			$sql = file_get_contents($sql_file);
			// Jalankan query-query insert
			$queries = explode(';', $sql);
			foreach ($queries as $q)
			{
				$q = trim($q);
				if ($q !== '' && strpos($q, 'INSERT INTO') !== FALSE)
				{
					$this->db->query($q);
				}
			}
			echo "[SUKSES] 10 data dummy (5 instansi + 5 siswa) berhasil dimasukkan!\n";
		}
		else
		{
			echo "[ERROR] File SQL tidak ditemukan.\n";
		}
	}
}
