-- =============================================================================
-- DATA AKUN TESTING PERSURATAN SMK
-- Email yang sama: noxapremia02@gmail.com
-- No. WA yang sama: +62 877-4177-7837
-- Password default semua akun testing: testing123
-- Bcrypt Hash: $2y$10$ctRwUdfOit.mI.dze23cBuBljAmU/NvZbK.tG1sfsfVaJqMSJU6Aa
-- =============================================================================

-- 1. Akun Kepala Sekolah (Untuk Pengesahan & Tanda Tangan Surat Keluar)
INSERT INTO `users` (
  `username`, `password_hash`, `full_name`, `nip`, `position`, 
  `phone`, `email`, `role`, `is_active`, `created_at`
) VALUES (
  'test_kepsek',
  '$2y$10$ctRwUdfOit.mI.dze23cBuBljAmU/NvZbK.tG1sfsfVaJqMSJU6Aa',
  'Drs. H. Mulyadi Pratama, M.Pd',
  '19720315 199802 1 003',
  'Kepala Sekolah',
  '+62 877-4177-7837',
  'noxapremia02@gmail.com',
  'kepala_sekolah',
  1,
  NOW()
) ON DUPLICATE KEY UPDATE 
  `password_hash` = VALUES(`password_hash`),
  `full_name`     = VALUES(`full_name`),
  `nip`           = VALUES(`nip`),
  `position`      = VALUES(`position`),
  `phone`         = VALUES(`phone`),
  `email`         = VALUES(`email`),
  `role`          = VALUES(`role`),
  `is_active`     = 1;

-- 2. Akun Tata Usaha (Untuk Pembuatan Surat, Layanan Siswa & Stempel Resmi TU)
INSERT INTO `users` (
  `username`, `password_hash`, `full_name`, `nip`, `position`, 
  `phone`, `email`, `role`, `is_active`, `created_at`
) VALUES (
  'test_tu',
  '$2y$10$ctRwUdfOit.mI.dze23cBuBljAmU/NvZbK.tG1sfsfVaJqMSJU6Aa',
  'Noxa Anindya, S.AP',
  '19890520 201403 2 006',
  'Staf Administrasi & Persuratan TU',
  '+62 877-4177-7837',
  'noxapremia02@gmail.com',
  'tu',
  1,
  NOW()
) ON DUPLICATE KEY UPDATE 
  `password_hash` = VALUES(`password_hash`),
  `full_name`     = VALUES(`full_name`),
  `nip`           = VALUES(`nip`),
  `position`      = VALUES(`position`),
  `phone`         = VALUES(`phone`),
  `email`         = VALUES(`email`),
  `role`          = VALUES(`role`),
  `is_active`     = 1;

-- 3. Akun Pegawai / Guru (Untuk Penerima Disposisi & Arsip Surat)
INSERT INTO `users` (
  `username`, `password_hash`, `full_name`, `nip`, `position`, 
  `phone`, `email`, `role`, `is_active`, `created_at`
) VALUES (
  'test_pegawai',
  '$2y$10$ctRwUdfOit.mI.dze23cBuBljAmU/NvZbK.tG1sfsfVaJqMSJU6Aa',
  'Premia Bagus Saputra, S.Kom',
  '19920810 201902 1 007',
  'Guru Produktif / Ka. Bengkel TKJ',
  '+62 877-4177-7837',
  'noxapremia02@gmail.com',
  'pegawai',
  1,
  NOW()
) ON DUPLICATE KEY UPDATE 
  `password_hash` = VALUES(`password_hash`),
  `full_name`     = VALUES(`full_name`),
  `nip`           = VALUES(`nip`),
  `position`      = VALUES(`position`),
  `phone`         = VALUES(`phone`),
  `email`         = VALUES(`email`),
  `role`          = VALUES(`role`),
  `is_active`     = 1;

-- 4. Akun Superuser / Administrator (Akses Penuh Semua Pengaturan & Audit Log)
INSERT INTO `users` (
  `username`, `password_hash`, `full_name`, `nip`, `position`, 
  `phone`, `email`, `role`, `is_active`, `created_at`
) VALUES (
  'test_admin',
  '$2y$10$ctRwUdfOit.mI.dze23cBuBljAmU/NvZbK.tG1sfsfVaJqMSJU6Aa',
  'Administrator Penguji Sistem',
  '19850101 201001 1 001',
  'Superuser Administrator',
  '+62 877-4177-7837',
  'noxapremia02@gmail.com',
  'superuser',
  1,
  NOW()
) ON DUPLICATE KEY UPDATE 
  `password_hash` = VALUES(`password_hash`),
  `full_name`     = VALUES(`full_name`),
  `nip`           = VALUES(`nip`),
  `position`      = VALUES(`position`),
  `phone`         = VALUES(`phone`),
  `email`         = VALUES(`email`),
  `role`          = VALUES(`role`),
  `is_active`     = 1;

-- Opsional: Jika ingin meng-update semua akun bawaan (admin, kepsek, tu, pegawai1) dengan email & WA yang sama:
UPDATE `users` SET 
  `phone` = '+62 877-4177-7837', 
  `email` = 'noxapremia02@gmail.com'
WHERE `username` IN ('admin', 'superuser', 'kepsek', 'tu', 'pegawai1', 'pegawai2');
