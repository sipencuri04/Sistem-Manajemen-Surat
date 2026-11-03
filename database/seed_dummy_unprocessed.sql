-- =============================================================================
-- DATA DUMMY PERSURATAN: 2 SURAT BELUM DIPROSES OLEH TU
-- SMK MUHAMMADIYAH BANDONGAN
-- =============================================================================

-- 1. SURAT DARI SISTEM (Permohonan Surat Mandiri Siswa - Status: pending / Menunggu)
-- Muncul di Menu: Permohonan Siswa (/permohonan-masuk)
INSERT INTO `letter_requests` (
  `tracking_code`,
  `letter_type_id`,
  `requester_name`,
  `requester_class`,
  `requester_phone`,
  `requester_email`,
  `content_data`,
  `requester_note`,
  `status`,
  `ip_address`,
  `created_at`
) VALUES (
  'MHB9284TKJ',
  1,
  'Muhammad Farhan Al-Fatih',
  'XII TKJ 1',
  '082134567890',
  'farhan.tkj@gmail.com',
  '{\"nama\":\"Muhammad Farhan Al-Fatih\",\"nis\":\"10842\",\"nisn\":\"0068492011\",\"tempat_lahir\":\"Magelang\",\"tanggal_lahir\":\"2008-05-14\",\"kelas\":\"XII TKJ 1\",\"jurusan\":\"Teknik Komputer dan Jaringan\",\"nama_orang_tua\":\"Hendra Wijaya\",\"keperluan\":\"Persyaratan Pengajuan Beasiswa Pendidikan Prestasi & Bantuan Kuliah Tahun 2026\",\"tahun_pelajaran\":\"2025/2026\"}',
  'Mohon dibantu proses segera Bapak/Ibu TU, berkas beasiswa harus diunggah paling lambat besok lusa. Terima kasih banyak.',
  'pending',
  '127.0.0.1',
  NOW()
);

-- 2. SURAT DARI INSTANSI (Surat Masuk dari Instansi Luar - Status: baru / Baru Diterima)
-- Muncul di Menu: Surat Masuk (/surat-masuk)
INSERT INTO `incoming_letters` (
  `agenda_number`,
  `letter_number`,
  `letter_date`,
  `received_date`,
  `sender`,
  `recipient`,
  `subject`,
  `description`,
  `status`,
  `is_external`,
  `tracking_code`,
  `sender_phone`,
  `sender_email`,
  `created_by`,
  `created_at`
) VALUES (
  CONCAT('SM-', LPAD(COALESCE((SELECT COUNT(*) + 1 FROM (SELECT id FROM incoming_letters) AS tmp), 1), 3, '0'), '/', YEAR(CURDATE())),
  '421.3/1084/Disdikbud/2026',
  CURDATE() - INTERVAL 1 DAY,
  CURDATE(),
  'Dinas Pendidikan dan Kebudayaan Cabang Wilayah V Prov. Jawa Tengah',
  'Kepala SMK Muhammadiyah Bandongan',
  'Undangan Rapat Koordinasi dan Verifikasi Tempat Uji Kompetensi Keahlian (TUK) SMK Tahun 2026',
  'Mengharap kehadiran Kepala Sekolah dan 1 (satu) orang Ketua Kompetensi Keahlian TKJ/TKR pada hari Kamis, pukul 08.30 WIB di Aula Balai Pengendali Pendidikan Menengah perihal Verifikasi Kelayakan TUK UKK Tahun 2026.',
  'baru',
  1,
  CONCAT('SM-', SUBSTRING(MD5(RAND()), 1, 6)),
  '0293-362145',
  'cabdin5.pdkjateng@gmail.com',
  4,
  NOW()
);
