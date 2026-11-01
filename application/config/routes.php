<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| ROUTES - Sistem Manajemen Persuratan SMK
| -------------------------------------------------------------------------
*/
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Autentikasi
$route['login']  = 'auth/login';
$route['logout'] = 'auth/logout';

// Surat
$route['surat']                          = 'letters/index';
$route['surat/buat']                     = 'letters/select_type';
$route['surat/buat/(:num)']              = 'letters/create/$1';
$route['surat/simpan/(:num)']            = 'letters/store/$1';
$route['surat/pratinjau']                = 'letters/preview';
$route['surat/detail/(:num)']            = 'letters/detail/$1';
$route['surat/edit/(:num)']              = 'letters/edit/$1';
$route['surat/update/(:num)']            = 'letters/update/$1';
$route['surat/hapus/(:num)']             = 'letters/delete/$1';
$route['surat/pdf/(:num)']               = 'letters/pdf/$1';
$route['surat/pdf/(:num)/(:any)']        = 'letters/pdf/$1/$2';
$route['surat/arsipkan-pdf/(:num)']      = 'letters/regenerate/$1';
$route['surat/nomor-berikutnya/(:num)']  = 'letters/next_number/$1';

// Portal publik permohonan surat oleh siswa (tanpa login)
$route['permohonan']                     = 'permohonan/index';
$route['permohonan/ajukan/(:num)']       = 'permohonan/form/$1';
$route['permohonan/kirim/(:num)']        = 'permohonan/kirim/$1';
$route['permohonan/cek']                 = 'permohonan/cek';
$route['permohonan/dokumen/(:any)/(:any)'] = 'permohonan/dokumen/$1/$2';
$route['permohonan/dokumen/(:any)']        = 'permohonan/dokumen/$1';

// Layanan publik kirim surat masuk oleh instansi luar / kedinasan (tanpa login)
$route['kirim-surat']                    = 'kirim_surat/index';
$route['kirim-surat/simpan']             = 'kirim_surat/simpan';
$route['kirim-surat/status']             = 'kirim_surat/status';
$route['kirim-surat/status/(:any)']      = 'kirim_surat/status/$1';

// Pengelolaan permohonan oleh TU (wajib login)
$route['permohonan-masuk']               = 'requests/index';
$route['permohonan-masuk/(:num)']        = 'requests/detail/$1';
$route['permohonan-masuk/tolak/(:num)']  = 'requests/reject/$1';
$route['permohonan-masuk/diambil/(:num)'] = 'requests/taken/$1';

// Arsip
$route['arsip']                          = 'archive/index';
$route['arsip/file/(:num)']              = 'archive/file/$1';

// Jenis surat
$route['jenis-surat']                    = 'letter_types/index';
$route['jenis-surat/tambah']             = 'letter_types/create';
$route['jenis-surat/edit/(:num)']        = 'letter_types/edit/$1';
$route['jenis-surat/hapus/(:num)']       = 'letter_types/delete/$1';

// Pengaturan
$route['pengaturan']                     = 'settings/index';
$route['pengaturan/tes-email']           = 'settings/test_email';
$route['pengaturan/hapus-gambar/(:any)'] = 'settings/remove_image/$1';
$route['akun']                           = 'account/index';

// Media terlindungi (logo, ttd, stempel)
$route['media/(:any)']                   = 'media/image/$1';

// Surat Masuk
$route['surat-masuk']                     = 'incoming_letters/index';
$route['surat-masuk/catat']               = 'incoming_letters/create';
$route['surat-masuk/simpan']              = 'incoming_letters/store';
$route['surat-masuk/detail/(:num)']       = 'incoming_letters/detail/$1';
$route['surat-masuk/edit/(:num)']         = 'incoming_letters/edit/$1';
$route['surat-masuk/update/(:num)']       = 'incoming_letters/update/$1';
$route['surat-masuk/hapus/(:num)']        = 'incoming_letters/delete/$1';
$route['surat-masuk/teruskan/(:num)']     = 'incoming_letters/forward_to_kepsek/$1';
$route['surat-masuk/berkas/(:num)']       = 'incoming_letters/file/$1';

// Disposisi & Delegasi
$route['disposisi']                       = 'dispositions/index';
$route['disposisi-saya']                  = 'dispositions/my_dispositions';
$route['disposisi/buat']                  = 'dispositions/create';
$route['disposisi/buat/(:num)']           = 'dispositions/create/$1';
$route['disposisi/simpan']                = 'dispositions/store';
$route['disposisi/detail/(:num)']         = 'dispositions/detail/$1';
$route['disposisi/update-progres/(:num)'] = 'dispositions/update_progress/$1';
$route['disposisi/delegasikan/(:num)']    = 'dispositions/delegate/$1';
$route['disposisi/bukti/(:num)']          = 'dispositions/proof_file/$1';

// Persetujuan Surat (Kepala Sekolah)
$route['persetujuan-surat']               = 'approvals/index';
$route['persetujuan-surat/setujui/(:num)'] = 'approvals/approve/$1';
$route['persetujuan-surat/tolak/(:num)']   = 'approvals/reject/$1';

// Manajemen Pengguna (Superuser & Admin)
$route['pengguna']                        = 'users/index';
$route['pengguna/tambah']                 = 'users/create';
$route['pengguna/simpan']                 = 'users/store';
$route['pengguna/edit/(:num)']           = 'users/edit/$1';
$route['pengguna/update/(:num)']         = 'users/update/$1';
$route['pengguna/reset-password/(:num)'] = 'users/reset_password/$1';
$route['pengguna/status/(:num)']         = 'users/toggle_status/$1';

// Notifikasi Internal
$route['notifikasi']                      = 'notifications/index';
$route['notifikasi/baca/(:num)']          = 'notifications/mark_as_read/$1';
$route['notifikasi/baca-semua']           = 'notifications/mark_all_read';

// Log & Backup Sistem (Superuser)
$route['log-aktivitas']                   = 'logs/audit';
$route['log-sistem']                      = 'logs/system';
$route['backup']                          = 'logs/backup';
$route['backup/buat']                     = 'logs/create_backup';
$route['backup/unduh/(:any)']             = 'logs/download_backup/$1';
