-- =============================================================================
-- RESET PERSURATAN & SEED 10 DATA DUMMY BELUM DIPROSES TU
-- SMK MUHAMMADIYAH BANDONGAN
-- Kontak Pengujian:
--   No. WhatsApp : +62 877-4177-7837
--   Email / Gmail: noxapremia02@gmail.com
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. HAPUS SEMUA DATA SURAT LAMA TERLEBIH DAHULU (MENGGUNAKAN DELETE AGAR AMAN DARI ERROR FOREIGN KEY #1701)
DELETE FROM `letter_files`;
DELETE FROM `letter_counters`;
DELETE FROM `disposition_activities`;
DELETE FROM `disposition_recipients`;
DELETE FROM `dispositions`;
DELETE FROM `letter_requests`;
DELETE FROM `incoming_letters`;
DELETE FROM `letters`;
DELETE FROM `queue_tickets`;
DELETE FROM `notifications` WHERE `type` IN ('surat_masuk', 'persetujuan', 'disposisi', 'permohonan');

-- Reset Auto Increment ID kembali ke 1
ALTER TABLE `letter_files` AUTO_INCREMENT = 1;
ALTER TABLE `disposition_activities` AUTO_INCREMENT = 1;
ALTER TABLE `disposition_recipients` AUTO_INCREMENT = 1;
ALTER TABLE `dispositions` AUTO_INCREMENT = 1;
ALTER TABLE `letter_requests` AUTO_INCREMENT = 1;
ALTER TABLE `incoming_letters` AUTO_INCREMENT = 1;
ALTER TABLE `letters` AUTO_INCREMENT = 1;
ALTER TABLE `queue_tickets` AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- 2. SEED 5 DATA SURAT DARI INSTANSI LUAR (Status: baru / Belum Diproses TU)
-- Masuk ke Menu: Surat Masuk (/surat-masuk)
-- =============================================================================

INSERT INTO `incoming_letters` (
  `agenda_number`, `letter_number`, `letter_date`, `received_date`,
  `sender`, `recipient`, `subject`, `description`, `status`,
  `is_external`, `tracking_code`, `sender_phone`, `sender_email`,
  `created_by`, `created_at`
) VALUES 
(
  'SM-001/2026', '421.3/1084/Disdikbud.V/2026', CURDATE() - INTERVAL 1 DAY, CURDATE(),
  'Dinas Pendidikan dan Kebudayaan Cabang Wilayah V Prov. Jawa Tengah', 'Kepala SMK Muhammadiyah Bandongan',
  'Undangan Rapat Koordinasi dan Verifikasi Tempat Uji Kompetensi Keahlian (TUK) SMK 2026',
  'Mengharap kehadiran Kepala Sekolah dan 1 orang Ketua Kompetensi Keahlian TKJ/TKR pada hari Kamis pukul 08.30 WIB di Aula Balai Pengendali Pendidikan Menengah.',
  'baru', 1, 'SM-INST01', '+62 877-4177-7837', 'noxapremia02@gmail.com', 4, NOW()
),
(
  'SM-002/2026', 'TEL.120/HK.210/WTL-MGL/2026', CURDATE() - INTERVAL 2 DAY, CURDATE(),
  'PT Telekomunikasi Indonesia Tbk (Telkom Witel Magelang)', 'Kepala SMK Muhammadiyah Bandongan',
  'Pemberitahuan Program Magang Industri & Sertifikasi Fiber Optik Siswa SMK Periode 2026/2027',
  'Penawaran kuota magang industri bagi 6 siswa jurusan TKJ di lingkungan Telkom Magelang serta fasilitasi uji kompetensi teknisi instalasi fiber optik.',
  'baru', 1, 'SM-INST02', '+62 877-4177-7837', 'noxapremia02@gmail.com', 4, NOW()
),
(
  'SM-003/2026', '0942/B7.3/PP.01/2026', CURDATE() - INTERVAL 3 DAY, CURDATE(),
  'Balai Besar Penjaminan Mutu Pendidikan (BBPMP) Provinsi Jawa Tengah', 'Kepala SMK Muhammadiyah Bandongan',
  'Permohonan Pengisian Instrumen Asesmen Kesiapan Pembelajaran Berbasis Digital Tahun 2026',
  'Permohonan kepada Tim Penjaminan Mutu dan Kepala Tata Usaha untuk mengisi instrumen evaluasi sarana digital sekolah selambat-lambatnya tanggal 10 November 2026.',
  'baru', 1, 'SM-INST03', '+62 877-4177-7837', 'noxapremia02@gmail.com', 4, NOW()
),
(
  'SM-004/2026', 'B/312/XI/OPS.1.1/2026/Sek.Bdg', CURDATE() - INTERVAL 1 DAY, CURDATE(),
  'Kepolisian Sektor Bandongan - Resor Magelang Kota', 'Kepala SMK Muhammadiyah Bandongan',
  'Undangan Sosialisasi Tertib Berlalu Lintas & Pembinaan Anti-Tawuran Pelajar',
  'Pemberitahuan pelaksanaan kegiatan Police Goes to School yang akan dilaksanakan pada saat upacara hari Senin depan serta penyuluhan keselamatan berkendara bagi siswa SMK.',
  'baru', 1, 'SM-INST04', '+62 877-4177-7837', 'noxapremia02@gmail.com', 4, NOW()
),
(
  'SM-005/2026', '582/II.3.AU/F/2026', CURDATE() - INTERVAL 2 DAY, CURDATE(),
  'Universitas Muhammadiyah Magelang (UNIMMA)', 'Kepala SMK Muhammadiyah Bandongan',
  'Penawaran Program Beasiswa Prestasi Jalur Khusus Kader Muhammadiyah Tahun 2026',
  'Informasi jalur beasiswa bebas biaya kuliah penuh (4 tahun) bagi 3 lulusan terbaik SMK Muhammadiyah Bandongan yang aktif di organisasi IPM.',
  'baru', 1, 'SM-INST05', '+62 877-4177-7837', 'noxapremia02@gmail.com', 4, NOW()
);

-- =============================================================================
-- 3. SEED 5 PERMOHONAN SURAT SISWA DARI SISTEM (Status: pending / Menunggu TU)
-- =============================================================================

INSERT INTO `letter_requests` (
  `tracking_code`, `letter_type_id`, `requester_name`, `requester_class`,
  `requester_phone`, `requester_email`, `content_data`, `requester_note`,
  `status`, `ip_address`, `created_at`
) VALUES 
(
  'REQ-NOXA01', 1, 'Noxa Aulia Pratama', 'XII TKJ 1', '+62 877-4177-7837', 'noxapremia02@gmail.com',
  '{"nama_siswa":"Noxa Aulia Pratama","nama":"Noxa Aulia Pratama","nis":"10895","nisn":"0068492088","tempat_lahir":"Magelang","tanggal_lahir":"2008-04-18","jenis_kelamin":"Laki-laki","kelas":"XII TKJ 1","tahun_pelajaran":"2025/2026","kompetensi_keahlian":"Teknik Komputer dan Jaringan","jurusan":"Teknik Komputer dan Jaringan","nama_orang_tua":"Bambang Premia S.","alamat":"Jl. Bandongan No. 45, RT 02/RW 03, Bandongan, Magelang","keperluan":"Persyaratan Pengajuan Beasiswa Pendidikan Prestasi & Bantuan Kuliah"}',
  'Mohon dibantu proses surat keterangan aktif ini Bapak/Ibu TU, berkas beasiswa harus segera saya unggah. Terima kasih.',
  'pending', '127.0.0.1', NOW()
),
(
  'REQ-PREM02', 11, 'Premia Bagus Saputra', 'XII TKR 2', '+62 877-4177-7837', 'noxapremia02@gmail.com',
  '{"nama_siswa":"Premia Bagus Saputra","nama":"Premia Bagus Saputra","nis":"10912","kelas":"XII TKR 2","kompetensi_keahlian":"Teknik Kendaraan Ringan Otomotif","jurusan":"Teknik Kendaraan Ringan Otomotif","nama_instansi":"PT Nasmoco Magelang","alamat_instansi":"Jl. Urip Sumoharjo No. 88, Magelang","alamat":"Jl. Urip Sumoharjo No. 88, Magelang","tanggal_mulai":"2025-07-01","tanggal_selesai":"2025-12-31","predikat":"Sangat Baik (A)","keperluan":"Kelengkapan Berkas Lamaran Pekerjaan di Industri Otomotif"}',
  'Untuk lampiran melamar kerja di bengkel resmi, mohon surat selesai PKL-nya diproses. Terima kasih banyak TU.',
  'pending', '127.0.0.1', NOW()
),
(
  'REQ-ANIN03', 1, 'Anindya Zahra Kirana', 'XI TKJ 2', '+62 877-4177-7837', 'noxapremia02@gmail.com',
  '{"nama_siswa":"Anindya Zahra Kirana","nama":"Anindya Zahra Kirana","nis":"11045","nisn":"0078129034","tempat_lahir":"Semarang","tanggal_lahir":"2009-02-14","jenis_kelamin":"Perempuan","kelas":"XI TKJ 2","tahun_pelajaran":"2025/2026","kompetensi_keahlian":"Teknik Komputer dan Jaringan","jurusan":"Teknik Komputer dan Jaringan","nama_orang_tua":"Agus Prasetyo","alamat":"Dusun Trasan, RT 01/RW 04, Bandongan, Magelang","keperluan":"Pengurusan Tunjangan Gaji Anak pada Instansi Tempat Kerja Orang Tua (KP4)"}',
  'Surat aktif untuk syarat KP4 tunjangan keluarga instansi ayah saya. Mohon bantuannya Bapak/Ibu.',
  'pending', '127.0.0.1', NOW()
),
(
  'REQ-RIZK04', 2, 'Rizky Aditya Nugroho', 'XII TBSM 1', '+62 877-4177-7837', 'noxapremia02@gmail.com',
  '{"nama_siswa":"Rizky Aditya Nugroho","nama":"Rizky Aditya Nugroho","nis":"10834","nisn":"0067451299","tempat_lahir":"Magelang","tanggal_lahir":"2008-01-30","nama_orang_tua":"Supriyadi","kompetensi_keahlian":"Teknik Bisnis Sepeda Motor","jurusan":"Teknik Bisnis Sepeda Motor","tahun_pelajaran":"2025/2026","tanggal_kelulusan":"2026-05-08","nomor_ijazah":"DN-03/M-SMK/K13/2026/012984","keperluan":"Persyaratan Pendaftaran Calon Bintara TNI Angkatan Darat"}',
  'Surat keterangan untuk pendaftaran Secaba TNI AD di Kodim, batas akhir pengumpulan minggu ini. Terima kasih.',
  'pending', '127.0.0.1', NOW()
),
(
  'REQ-SALS05', 1, 'Salsabila Putri Maharani', 'X AKL 1', '+62 877-4177-7837', 'noxapremia02@gmail.com',
  '{"nama_siswa":"Salsabila Putri Maharani","nama":"Salsabila Putri Maharani","nis":"11280","nisn":"0089123847","tempat_lahir":"Yogyakarta","tanggal_lahir":"2010-09-05","jenis_kelamin":"Perempuan","kelas":"X AKL 1","tahun_pelajaran":"2025/2026","kompetensi_keahlian":"Akuntansi dan Keuangan Lembaga","jurusan":"Akuntansi dan Keuangan Lembaga","nama_orang_tua":"Siti Rohmah","alamat":"Desa Rejosari, RT 03/RW 01, Bandongan, Magelang","keperluan":"Pembuatan Rekening Tabungan SimPel Bank Jateng dan Aktivasi Kartu Indonesia Pintar (KIP)"}',
  'Untuk aktivasi buku tabungan Bank Jateng pencairan dana bantuan KIP. Mohon bantuannya Bapak/Ibu TU.',
  'pending', '127.0.0.1', NOW()
);
