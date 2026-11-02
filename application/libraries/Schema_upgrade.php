<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pembaruan struktur database otomatis untuk instalasi lama.
 *
 * Versi 3:
 * - Sistem 5 Role (Siswa, Pegawai, Kepala Sekolah, TU, Superuser)
 * - Surat Masuk (incoming_letters)
 * - Disposisi Surat & Pelaporan Progres (dispositions, disposition_recipients, disposition_activities)
 * - Notifikasi Internal (notifications)
 * - Audit Log (audit_logs)
 * - Alur Persetujuan Surat Keluar (letters.approval_status)
 */
class Schema_upgrade
{
	const VERSION = 8;

	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	protected function flag_file()
	{
		return APPPATH.'cache/schema_v'.self::VERSION.'.flag';
	}

	public function run()
	{
		if (is_file($this->flag_file()))
		{
			return TRUE;
		}

		$db = $this->CI->db;
		$debug = $db->db_debug;
		$db->db_debug = FALSE;
		$ok = TRUE;

		// 1. letter_types: allow_public
		if ( ! $db->field_exists('allow_public', 'letter_types'))
		{
			$ok = $db->query("ALTER TABLE `letter_types` ADD COLUMN `allow_public` TINYINT(1) NOT NULL DEFAULT 0
				COMMENT '1 = siswa dapat mengajukan lewat formulir publik' AFTER `is_active`") && $ok;
			if ($ok)
			{
				$db->query("UPDATE `letter_types` SET `allow_public` = 1 WHERE `code` IN ('SKA', 'SKL', 'SKPKL')");
			}
		}

		// 2. letter_requests
		if ( ! $db->table_exists('letter_requests'))
		{
			$sql = @file_get_contents(FCPATH.'database/update_permohonan_siswa.sql');
			if ($sql && preg_match('/CREATE TABLE `letter_requests`.*?;\s*$/ms', (string) $sql, $m))
			{
				$ok = $db->query($m[0]) && $ok;
			}
		}
		elseif ( ! $db->field_exists('requester_email', 'letter_requests'))
		{
			$ok = $db->query("ALTER TABLE `letter_requests` ADD COLUMN `requester_email` VARCHAR(150) NULL DEFAULT NULL COMMENT 'Email/Gmail Siswa' AFTER `requester_phone`") && $ok;
		}

		// 3. Kolom profil & role di tabel users
		if ($db->table_exists('users'))
		{
			if ( ! $db->field_exists('nip', 'users'))
			{
				$db->query("ALTER TABLE `users` ADD COLUMN `nip` VARCHAR(30) NULL DEFAULT NULL AFTER `full_name`");
			}
			if ( ! $db->field_exists('position', 'users'))
			{
				$db->query("ALTER TABLE `users` ADD COLUMN `position` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Jabatan' AFTER `nip`");
			}
			if ( ! $db->field_exists('phone', 'users'))
			{
				$db->query("ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(30) NULL DEFAULT NULL AFTER `position`");
			}
		}

		// 4. Tabel incoming_letters (Surat Masuk)
		if ( ! $db->table_exists('incoming_letters'))
		{
			$db->query("CREATE TABLE IF NOT EXISTS `incoming_letters` (
			  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `agenda_number`    VARCHAR(50)  NOT NULL COMMENT 'Nomor agenda surat masuk',
			  `letter_number`    VARCHAR(100) NOT NULL COMMENT 'Nomor surat dari instansi pengirim',
			  `letter_date`      DATE         NOT NULL COMMENT 'Tanggal yang tertera di surat',
			  `received_date`    DATE         NOT NULL COMMENT 'Tanggal surat diterima di sekolah',
			  `sender`           VARCHAR(200) NOT NULL COMMENT 'Asal / pengirim surat',
			  `recipient`        VARCHAR(200) NULL DEFAULT NULL COMMENT 'Tujuan / ditujukan kepada',
			  `subject`          VARCHAR(255) NOT NULL COMMENT 'Perihal surat',
			  `description`      TEXT         NULL DEFAULT NULL COMMENT 'Ringkasan isi surat',
			  `file_path`        VARCHAR(255) NULL DEFAULT NULL COMMENT 'Path file scan berkas di storage/',
			  `file_name`        VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nama file asli saat diunggah',
			  `status`           VARCHAR(20)  NOT NULL DEFAULT 'baru' COMMENT 'baru | diteruskan | didisposisi | selesai | diarsipkan',
			  `is_external`          TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = dikirim lewat portal publik instansi',
			  `tracking_code`        VARCHAR(20)  NULL DEFAULT NULL COMMENT 'Kode resi tanda terima instansi',
			  `sender_phone`         VARCHAR(30)  NULL DEFAULT NULL COMMENT 'Nomor kontak PIC pengirim',
			  `sender_email`         VARCHAR(100) NULL DEFAULT NULL COMMENT 'Email PIC pengirim',
			  `created_by`       INT UNSIGNED NULL DEFAULT NULL,
			  `created_at`       DATETIME     NOT NULL,
			  `updated_at`       DATETIME     NULL DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idx_incoming_agenda` (`agenda_number`),
			  KEY `idx_incoming_received` (`received_date`),
			  KEY `idx_incoming_status` (`status`),
			  KEY `idx_incoming_tracking` (`tracking_code`),
			  KEY `fk_incoming_user` (`created_by`),
			  CONSTRAINT `fk_incoming_user` FOREIGN KEY (`created_by`)
			    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}
		else
		{
			if ( ! $db->field_exists('is_external', 'incoming_letters'))
			{
				$db->query("ALTER TABLE `incoming_letters` ADD COLUMN `is_external` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = dikirim lewat portal publik' AFTER `status`");
			}
			if ( ! $db->field_exists('tracking_code', 'incoming_letters'))
			{
				$db->query("ALTER TABLE `incoming_letters` ADD COLUMN `tracking_code` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Kode tanda terima' AFTER `is_external`");
				$db->query("ALTER TABLE `incoming_letters` ADD KEY `idx_incoming_tracking` (`tracking_code`)");
			}
			if ( ! $db->field_exists('sender_phone', 'incoming_letters'))
			{
				$db->query("ALTER TABLE `incoming_letters` ADD COLUMN `sender_phone` VARCHAR(30) NULL DEFAULT NULL AFTER `tracking_code`");
			}
			if ( ! $db->field_exists('sender_email', 'incoming_letters'))
			{
				$db->query("ALTER TABLE `incoming_letters` ADD COLUMN `sender_email` VARCHAR(100) NULL DEFAULT NULL AFTER `sender_phone`");
			}
		}

		// 5. Tabel dispositions (Disposisi Surat)
		if ( ! $db->table_exists('dispositions'))
		{
			$db->query("CREATE TABLE IF NOT EXISTS `dispositions` (
			  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `incoming_letter_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Referensi surat masuk (jika ada)',
			  `outgoing_letter_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Referensi surat keluar (jika ada)',
			  `disposition_number` VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Nomor lembar disposisi',
			  `disposition_date`   DATE         NOT NULL COMMENT 'Tanggal disposisi diberikan',
			  `deadline_date`      DATE         NULL DEFAULT NULL COMMENT 'Tenggat waktu penyelesaian',
			  `priority`           VARCHAR(20)  NOT NULL DEFAULT 'biasa' COMMENT 'biasa | penting | segera | rahasia',
			  `instruction`        TEXT         NOT NULL COMMENT 'Instruksi kepala sekolah',
			  `notes`              TEXT         NULL DEFAULT NULL COMMENT 'Catatan tambahan',
			  `status`             VARCHAR(20)  NOT NULL DEFAULT 'menunggu' COMMENT 'menunggu | diproses | selesai | dibatalkan',
			  `created_by`         INT UNSIGNED NOT NULL COMMENT 'Kepala sekolah pembuat disposisi',
			  `created_at`         DATETIME     NOT NULL,
			  `updated_at`         DATETIME     NULL DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idx_disposition_deadline` (`deadline_date`),
			  KEY `idx_disposition_status` (`status`),
			  KEY `fk_disposition_incoming` (`incoming_letter_id`),
			  KEY `fk_disposition_outgoing` (`outgoing_letter_id`),
			  KEY `fk_disposition_creator` (`created_by`),
			  CONSTRAINT `fk_disposition_incoming` FOREIGN KEY (`incoming_letter_id`)
			    REFERENCES `incoming_letters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  CONSTRAINT `fk_disposition_outgoing` FOREIGN KEY (`outgoing_letter_id`)
			    REFERENCES `letters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  CONSTRAINT `fk_disposition_creator` FOREIGN KEY (`created_by`)
			    REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}

		// 6. Tabel disposition_recipients (Penerima Disposisi & Progres Pegawai)
		if ( ! $db->table_exists('disposition_recipients'))
		{
			$db->query("CREATE TABLE IF NOT EXISTS `disposition_recipients` (
			  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `disposition_id`     INT UNSIGNED NOT NULL,
			  `user_id`            INT UNSIGNED NOT NULL COMMENT 'Pegawai penerima disposisi',
			  `status`             VARCHAR(30)  NOT NULL DEFAULT 'menunggu' COMMENT 'menunggu | diproses | menunggu_informasi | selesai',
			  `progress_percent`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
			  `progress_notes`     TEXT         NULL DEFAULT NULL COMMENT 'Catatan laporan tindak lanjut pegawai',
			  `evidence_file`      VARCHAR(255) NULL DEFAULT NULL COMMENT 'Bukti hasil tindak lanjut di storage/',
			  `evidence_name`      VARCHAR(255) NULL DEFAULT NULL,
			  `completed_at`       DATETIME     NULL DEFAULT NULL,
			  `delegated_to`       INT UNSIGNED NULL DEFAULT NULL COMMENT 'Penerima delegasi ulang jika dialihkan',
			  `delegation_reason`  TEXT         NULL DEFAULT NULL,
			  `delegated_at`       DATETIME     NULL DEFAULT NULL,
			  `created_at`         DATETIME     NOT NULL,
			  `updated_at`         DATETIME     NULL DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `uq_disp_recipient` (`disposition_id`, `user_id`),
			  KEY `idx_disp_recip_status` (`status`),
			  KEY `fk_disp_recip_disp` (`disposition_id`),
			  KEY `fk_disp_recip_user` (`user_id`),
			  KEY `fk_disp_recip_delegated` (`delegated_to`),
			  CONSTRAINT `fk_disp_recip_disp` FOREIGN KEY (`disposition_id`)
			    REFERENCES `dispositions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  CONSTRAINT `fk_disp_recip_user` FOREIGN KEY (`user_id`)
			    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  CONSTRAINT `fk_disp_recip_delegated` FOREIGN KEY (`delegated_to`)
			    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}

		// 7. Tabel disposition_activities (Aktivitas & Riwayat Disposisi)
		if ( ! $db->table_exists('disposition_activities'))
		{
			$db->query("CREATE TABLE IF NOT EXISTS `disposition_activities` (
			  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `disposition_id` INT UNSIGNED NOT NULL,
			  `user_id`        INT UNSIGNED NULL DEFAULT NULL,
			  `action`         VARCHAR(50)  NOT NULL COMMENT 'dibuat | dilihat | update_progres | selesai | didelegasikan | diubah',
			  `description`    TEXT         NOT NULL,
			  `created_at`     DATETIME     NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `fk_disp_act_disp` (`disposition_id`),
			  KEY `fk_disp_act_user` (`user_id`),
			  CONSTRAINT `fk_disp_act_disp` FOREIGN KEY (`disposition_id`)
			    REFERENCES `dispositions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  CONSTRAINT `fk_disp_act_user` FOREIGN KEY (`user_id`)
			    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}

		// 8. Tabel notifications (Notifikasi Internal)
		if ( ! $db->table_exists('notifications'))
		{
			$db->query("CREATE TABLE IF NOT EXISTS `notifications` (
			  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `user_id`    INT UNSIGNED NOT NULL COMMENT 'Penerima notifikasi',
			  `title`      VARCHAR(150) NOT NULL,
			  `message`    TEXT         NOT NULL,
			  `link`       VARCHAR(255) NULL DEFAULT NULL,
			  `type`       VARCHAR(30)  NOT NULL DEFAULT 'info' COMMENT 'disposisi | persetujuan | permohonan | warning | info',
			  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
			  `created_at` DATETIME     NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idx_notif_user_read` (`user_id`, `is_read`, `created_at`),
			  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`)
			    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}

		// 9. Tabel audit_logs (Audit Trail Sensitif)
		if ( ! $db->table_exists('audit_logs'))
		{
			$db->query("CREATE TABLE IF NOT EXISTS `audit_logs` (
			  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `user_id`     INT UNSIGNED NULL DEFAULT NULL,
			  `username`    VARCHAR(50)  NULL DEFAULT NULL,
			  `action`      VARCHAR(100) NOT NULL,
			  `entity_type` VARCHAR(50)  NULL DEFAULT NULL,
			  `entity_id`   INT UNSIGNED NULL DEFAULT NULL,
			  `details`     TEXT         NULL DEFAULT NULL,
			  `ip_address`  VARCHAR(45)  NULL DEFAULT NULL,
			  `user_agent`  VARCHAR(255) NULL DEFAULT NULL,
			  `created_at`  DATETIME     NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idx_audit_user` (`user_id`),
			  KEY `idx_audit_action` (`action`),
			  KEY `idx_audit_entity` (`entity_type`, `entity_id`),
			  KEY `idx_audit_created` (`created_at`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}

		// 10. letters: persetujuan surat keluar
		if ($db->table_exists('letters'))
		{
			if ( ! $db->field_exists('approval_status', 'letters'))
			{
				$db->query("ALTER TABLE `letters` ADD COLUMN `approval_status` VARCHAR(30) NOT NULL DEFAULT 'disetujui'
					COMMENT 'draft | menunggu_persetujuan | disetujui | ditolak' AFTER `status`");
			}
			if ( ! $db->field_exists('approved_by', 'letters'))
			{
				$db->query("ALTER TABLE `letters` ADD COLUMN `approved_by` INT UNSIGNED NULL DEFAULT NULL AFTER `approval_status`");
			}
			if ( ! $db->field_exists('approved_at', 'letters'))
			{
				$db->query("ALTER TABLE `letters` ADD COLUMN `approved_at` DATETIME NULL DEFAULT NULL AFTER `approved_by`");
			}
			if ( ! $db->field_exists('rejection_reason', 'letters'))
			{
				$db->query("ALTER TABLE `letters` ADD COLUMN `rejection_reason` TEXT NULL DEFAULT NULL AFTER `approved_at`");
			}
			if ( ! $db->field_exists('signature_type', 'letters'))
			{
				$db->query("ALTER TABLE `letters` ADD COLUMN `signature_type` VARCHAR(20) NOT NULL DEFAULT 'digital'
					COMMENT 'digital = ttd & stempel sistem | basah = wajib ttd basah fisik' AFTER `status`");
			}
		}

		// 11. Kolom SMTP di school_settings agar bisa disetel langsung dari menu Pengaturan di web
		if ($db->table_exists('school_settings'))
		{
			if ( ! $db->field_exists('smtp_enabled', 'school_settings'))
			{
				$db->query("ALTER TABLE `school_settings` ADD COLUMN `smtp_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = aktifkan pengiriman email'");
			}
			if ( ! $db->field_exists('smtp_host', 'school_settings'))
			{
				$db->query("ALTER TABLE `school_settings` ADD COLUMN `smtp_host` VARCHAR(150) NULL DEFAULT 'smtp.gmail.com'");
			}
			if ( ! $db->field_exists('smtp_port', 'school_settings'))
			{
				$db->query("ALTER TABLE `school_settings` ADD COLUMN `smtp_port` INT(5) NOT NULL DEFAULT 587");
			}
			if ( ! $db->field_exists('smtp_crypto', 'school_settings'))
			{
				$db->query("ALTER TABLE `school_settings` ADD COLUMN `smtp_crypto` VARCHAR(10) NOT NULL DEFAULT 'tls'");
			}
			if ( ! $db->field_exists('smtp_user', 'school_settings'))
			{
				$db->query("ALTER TABLE `school_settings` ADD COLUMN `smtp_user` VARCHAR(150) NULL DEFAULT NULL COMMENT 'Alamat Gmail pengirim'");
			}
			if ( ! $db->field_exists('smtp_pass', 'school_settings'))
			{
				$db->query("ALTER TABLE `school_settings` ADD COLUMN `smtp_pass` VARCHAR(255) NULL DEFAULT NULL COMMENT 'App Password 16 huruf'");
			}
			if ( ! $db->field_exists('smtp_from_email', 'school_settings'))
			{
				$db->query("ALTER TABLE `school_settings` ADD COLUMN `smtp_from_email` VARCHAR(150) NULL DEFAULT NULL");
			}
			if ( ! $db->field_exists('smtp_from_name', 'school_settings'))
			{
				$db->query("ALTER TABLE `school_settings` ADD COLUMN `smtp_from_name` VARCHAR(150) NULL DEFAULT NULL");
			}
		}

		// 12. Folder storage aman
		$needed_dirs = array('incoming_letters', 'dispositions', 'backups');
		foreach ($needed_dirs as $sub)
		{
			$d = storage_path($sub);
			if ( ! is_dir($d))
			{
				@mkdir($d, 0755, TRUE);
			}
		}

		// 12. Akun demo untuk kelima role (bila belum ada)
		$this->seed_demo_roles($db);

		// 13. Injeksi Profil Resmi SMK Muhammadiyah Bandongan
		$this->seed_school_profile($db);

		$db->db_debug = $debug;
		$db->data_cache = array(); // reset cache CI

		@file_put_contents($this->flag_file(), date('c'));
		log_message('info', 'Pembaruan struktur database v'.self::VERSION.' selesai.');
		return TRUE;
	}

	/** Buat akun contoh untuk setiap role jika belum ada. */
	protected function seed_demo_roles($db)
	{
		$roles_to_seed = array(
			array(
				'username'  => 'superuser',
				'password'  => 'superuser123',
				'full_name' => 'Superuser Sistem / Developer',
				'role'      => 'superuser',
				'position'  => 'Administrator Sistem & TI',
				'email'     => 'superuser@sekolah.sch.id',
			),
			array(
				'username'  => 'kepsek',
				'password'  => 'kepsek123',
				'full_name' => 'Drs. H. Ahmad Dahlan, M.Pd',
				'nip'       => '19680512 199403 1 005',
				'role'      => 'kepala_sekolah',
				'position'  => 'Kepala Sekolah',
				'email'     => 'kepsek@sekolah.sch.id',
			),
			array(
				'username'  => 'tu',
				'password'  => 'tu123',
				'full_name' => 'Siti Rahmawati, S.AP',
				'nip'       => '19820715 200604 2 018',
				'role'      => 'tu',
				'position'  => 'Staf Tata Usaha / Urusan Persuratan',
				'email'     => 'tu@sekolah.sch.id',
			),
			array(
				'username'  => 'pegawai1',
				'password'  => 'pegawai123',
				'full_name' => 'Bambang Wijaya, S.Pd',
				'nip'       => '19850320 200902 1 004',
				'role'      => 'pegawai',
				'position'  => 'Guru / Koordinator Kurikulum',
				'email'     => 'bambang@sekolah.sch.id',
			),
			array(
				'username'  => 'pegawai2',
				'password'  => 'pegawai123',
				'full_name' => 'Dewi Lestari, S.Kom',
				'nip'       => '19901108 201503 2 007',
				'role'      => 'pegawai',
				'position'  => 'Guru / Kepala Program Keahlian TKJ',
				'email'     => 'dewi@sekolah.sch.id',
			),
		);

		foreach ($roles_to_seed as $u)
		{
			$exists = $db->where('username', $u['username'])->count_all_results('users') > 0;
			if ( ! $exists)
			{
				$db->insert('users', array(
					'username'      => $u['username'],
					'password_hash' => password_hash($u['password'], PASSWORD_DEFAULT),
					'full_name'     => $u['full_name'],
					'nip'           => isset($u['nip']) ? $u['nip'] : NULL,
					'position'      => isset($u['position']) ? $u['position'] : NULL,
					'email'         => isset($u['email']) ? $u['email'] : NULL,
					'role'          => $u['role'],
					'is_active'     => 1,
					'created_at'    => date('Y-m-d H:i:s'),
				));
			}
		}
	}

	/** Injeksi profil resmi SMK Muhammadiyah Bandongan bila masih kosong atau generic. */
	protected function seed_school_profile($db)
	{
		if ( ! $db->table_exists('school_settings')) return;

		$curr = $db->get_where('school_settings', array('id' => 1))->row_array();
		if ( ! $curr || empty($curr['school_name']) || $curr['school_name'] === 'Layanan Persuratan Sekolah' || strpos($curr['school_name'], 'Contoh') !== FALSE)
		{
			$school_data = array(
				'kop_line1'       => 'PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN MAGELANG',
				'kop_line2'       => 'MAJELIS PENDIDIKAN DASAR DAN MENENGAH',
				'school_name'     => 'SMK MUHAMMADIYAH BANDONGAN',
				'school_code'     => 'SMK-MUH',
				'npsn'            => '20307689',
				'address'         => 'Jl. Bandongan - Windusari Km. 1, Bandongan',
				'city'            => 'Kabupaten Magelang',
				'postal_code'     => '56152',
				'phone'           => '081234567800',
				'email'           => 'smkmuhbandongan@gmail.com',
				'website'         => 'smkmuhbandongan.sch.id',
				'kop_template'    => 'logo_kiri',
				'headmaster_name' => 'Drs. H. Ahmad Dahlan, M.Pd',
				'headmaster_nip'  => '19680512 199403 1 005',
				'signer_title'    => 'Kepala Sekolah',
				'show_nip'        => 1,
				'number_format'   => '421.5/{NO}/SMK-MUH/{BULAN_ROMAWI}/{TAHUN}',
				'number_padding'  => 3,
				'number_reset'    => 'yearly',
				'number_scope'    => 'global',
				'updated_at'      => date('Y-m-d H:i:s'),
			);

			if ($curr)
			{
				$db->where('id', 1)->update('school_settings', $school_data);
			}
			else
			{
				$school_data['id'] = 1;
				$db->insert('school_settings', $school_data);
			}
		}
	}

}
