<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-10-28 08:40:46 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 08:40:46 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 08:40:50 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 08:40:50 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 08:40:55 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 08:40:55 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 08:41:07 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 08:41:07 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 08:41:07 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:07:40 --> Severity: error --> Exception: Call to undefined method CI_Loader::has_role() D:\laragon\www\Sistem-Manajemen-Surat\application\views\incoming\index.php 7
ERROR - 2026-10-28 21:10:03 --> Query error: Unknown column 'reference_number' in 'field list' - Invalid query: INSERT INTO `incoming_letters` (`reference_number`, `sender`, `letter_date`, `received_date`, `subject`, `status`, `forwarded_to`, `forwarded_at`, `created_by`, `created_at`) VALUES ('SM/TEST/1793196603', 'Dinas Pendidikan Jawa Barat', '2026-10-28', '2026-10-28', 'Undangan Rapat Koordinasi Kurikulum', 'diteruskan_ke_kepsek', '3', '2026-10-28 21:10:03', '4', '2026-10-28 21:10:03')
ERROR - 2026-10-28 21:11:09 --> Query error: Unknown column 'data' in 'field list' - Invalid query: INSERT INTO `letters` (`letter_type_id`, `letter_number`, `letter_date`, `place`, `subject`, `signer_title`, `signer_name`, `signer_nip`, `data`, `status`, `approval_status`, `created_by`, `created_at`) VALUES ('1', '421.5/TEST-APPROVAL/1793196669', '2026-10-28', 'Bandung', 'Surat Tugas Pengawasan Ujian', 'Kepala Sekolah', 'Drs. H. Maman Suratman, M.Pd.', '196801011993031005', '{\"content\":\"Test content\"}', 'draft', 'menunggu_persetujuan', '4', '2026-10-28 21:11:09')
ERROR - 2026-10-28 21:13:04 --> Query error: Table 'db_persuratan_smk.schools' doesn't exist - Invalid query: SELECT *
FROM `schools`
ERROR - 2026-10-28 21:13:49 --> Query error: Table 'db_persuratan_smk.school_profile' doesn't exist - Invalid query: SELECT *
FROM `school_profile`
ERROR - 2026-10-28 21:15:18 --> Severity: error --> Exception: Call to undefined method CI_Loader::has_role() D:\laragon\www\Sistem-Manajemen-Surat\application\views\incoming\index.php 7
ERROR - 2026-10-28 21:15:22 --> Severity: error --> Exception: Call to undefined method CI_Loader::has_role() D:\laragon\www\Sistem-Manajemen-Surat\application\views\incoming\index.php 7
ERROR - 2026-10-28 21:15:38 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '"selesai" ASC, `d`.`deadline_date` ASC, `r`.`id` DESC
 LIMIT 5' at line 7 - Invalid query: SELECT `r`.*, `d`.`disposition_number`, `d`.`disposition_date`, `d`.`deadline_date`, `d`.`priority`, `d`.`instruction`, `d`.`notes`, `d`.`created_by`, `i`.`agenda_number`, `i`.`letter_number` AS `incoming_letter_number`, `i`.`sender` AS `incoming_sender`, `i`.`subject` AS `incoming_subject`, `i`.`file_path` AS `incoming_file_path`, `creator`.`full_name` AS `creator_name`, `creator`.`position` AS `creator_position`
FROM `disposition_recipients` `r`
INNER JOIN `dispositions` `d` ON `d`.`id` = `r`.`disposition_id`
LEFT JOIN `users` `creator` ON `creator`.`id` = `d`.`created_by`
LEFT JOIN `incoming_letters` `i` ON `i`.`id` = `d`.`incoming_letter_id`
WHERE `r`.`user_id` = 5
ORDER BY `r`.`status =` "selesai" ASC, `d`.`deadline_date` ASC, `r`.`id` DESC
 LIMIT 5
ERROR - 2026-10-28 21:15:39 --> Severity: error --> Exception: Call to undefined method CI_Loader::is_kepala_sekolah() D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\index.php 7
ERROR - 2026-10-28 21:15:39 --> Severity: error --> Exception: Call to undefined method CI_Loader::has_role() D:\laragon\www\Sistem-Manajemen-Surat\application\views\incoming\index.php 7
ERROR - 2026-10-28 21:15:39 --> Severity: error --> Exception: Call to undefined method CI_Loader::has_role() D:\laragon\www\Sistem-Manajemen-Surat\application\views\incoming\index.php 7
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Undefined property: CI_Loader::$current_user D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Trying to access array offset on null D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Undefined property: CI_Loader::$current_user D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Trying to access array offset on null D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Undefined property: CI_Loader::$current_user D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Trying to access array offset on null D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Undefined property: CI_Loader::$current_user D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Trying to access array offset on null D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Undefined property: CI_Loader::$current_user D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Trying to access array offset on null D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Undefined property: CI_Loader::$current_user D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:15:40 --> Severity: Warning --> Trying to access array offset on null D:\laragon\www\Sistem-Manajemen-Surat\application\views\users\index.php 63
ERROR - 2026-10-28 21:16:52 --> Severity: error --> Exception: Call to undefined method CI_Loader::is_kepala_sekolah() D:\laragon\www\Sistem-Manajemen-Surat\application\views\letters\detail.php 28
ERROR - 2026-10-28 21:16:56 --> Severity: error --> Exception: Call to undefined method CI_Loader::is_kepala_sekolah() D:\laragon\www\Sistem-Manajemen-Surat\application\views\letters\detail.php 28
ERROR - 2026-10-28 21:25:51 --> Severity: Warning --> Undefined array key "incoming_letter_id" D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\my_dispositions.php 66
ERROR - 2026-10-28 21:25:51 --> Severity: Warning --> Undefined array key "incoming_letter_id" D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\my_dispositions.php 66
ERROR - 2026-10-28 21:28:41 --> Severity: Warning --> Undefined array key "incoming_letter_id" D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\my_dispositions.php 66
ERROR - 2026-10-28 21:28:41 --> Severity: Warning --> Undefined array key "incoming_letter_id" D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\my_dispositions.php 66
ERROR - 2026-10-28 21:30:56 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:30:56 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:30:59 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:30:59 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:31:03 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:31:03 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:31:06 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:31:50 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:31:50 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:31:50 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:34:33 --> 404 Page Not Found: Notifications/mark_as_read
ERROR - 2026-10-28 21:38:27 --> Severity: Warning --> Undefined array key "incoming_letter_id" D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\my_dispositions.php 66
ERROR - 2026-10-28 21:38:27 --> Severity: Warning --> Undefined array key "incoming_letter_id" D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\my_dispositions.php 66
ERROR - 2026-10-28 21:38:39 --> Severity: Warning --> Undefined array key "incoming_letter_id" D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\my_dispositions.php 66
ERROR - 2026-10-28 21:38:39 --> Severity: Warning --> Undefined array key "incoming_letter_id" D:\laragon\www\Sistem-Manajemen-Surat\application\views\dispositions\my_dispositions.php 66
ERROR - 2026-10-28 21:43:53 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:43:53 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:51:12 --> 404 Page Not Found: Assets/vendor
ERROR - 2026-10-28 21:51:12 --> 404 Page Not Found: Assets/vendor
