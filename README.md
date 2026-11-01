# Sistem Manajemen Persuratan SMK

Aplikasi web untuk staf Tata Usaha (TU) SMK: membuat surat resmi sekolah, penomoran otomatis, pratinjau, PDF A4, dan arsip digital.

**Teknologi:** PHP + CodeIgniter 3.1.13 · MySQL/MariaDB · Bootstrap 5.3 · Font Awesome 6 · Chart.js 4 · Dompdf 3.1

Semua aset (Bootstrap, Font Awesome, Chart.js, Dompdf) sudah disertakan di dalam paket, jadi aplikasi tetap bisa berjalan di jaringan lokal tanpa internet.

---

## Daftar Isi

1. [Persyaratan sistem](#1-persyaratan-sistem)
2. [Menempatkan source code di XAMPP/Laragon](#2-menempatkan-source-code)
3. [Membuat database](#3-membuat-database)
4. [Mengimpor file SQL](#4-mengimpor-file-sql)
5. [Mengatur `application/config/config.php`](#5-mengatur-applicationconfigconfigphp)
6. [Mengatur `application/config/database.php`](#6-mengatur-applicationconfigdatabasephp)
7. [Base URL & URL rewriting](#7-base-url--url-rewriting)
8. [Memasang Dompdf](#8-memasang-dompdf)
9. [Membuat akun administrator](#9-membuat-akun-administrator)
10. [Menjalankan & menguji aplikasi](#10-menjalankan--menguji-aplikasi)
11. [Memasang di shared hosting](#11-memasang-di-shared-hosting)
12. [Backup database & file surat](#12-backup-database--file-surat)
13. [Arsitektur aplikasi](#13-arsitektur-aplikasi)
14. [Rancangan database](#14-rancangan-database)
15. [Penomoran surat](#15-penomoran-surat)
16. [Menambah template surat (developer)](#16-menambah-template-surat-developer)
17. [Keamanan](#17-keamanan)
18. [Pemecahan masalah](#18-pemecahan-masalah)
19. [Permohonan surat oleh siswa](#19-permohonan-surat-oleh-siswa-tanpa-login)

---

## 1. Persyaratan sistem

| Komponen | Versi |
|---|---|
| PHP | **7.4 – 8.3** (diuji pada PHP 8.3) |
| MySQL / MariaDB | MySQL **5.7+** atau MariaDB **10.2+** (membutuhkan tipe data `JSON`) |
| Web server | Apache dengan `mod_rewrite` (XAMPP, Laragon, cPanel) |
| Ekstensi PHP | `mysqli`, `mbstring`, `gd`, `dom`, `xml`, `fileinfo`, `json`, `ctype`, `openssl` |

Di XAMPP/Laragon semua ekstensi di atas biasanya sudah aktif. Jika belum, buka `php.ini` lalu hapus tanda `;` di depan baris `extension=gd`, `extension=mbstring`, `extension=fileinfo`, `extension=mysqli`, kemudian restart Apache.

> Catatan PHP 8.2+: CodeIgniter 3 aslinya memunculkan pesan *deprecated* di PHP 8.2 ke atas. Paket ini sudah ditambal (atribut `#[\AllowDynamicProperties]` pada class inti di folder `system/`, juga satu perbaikan kecil di `Pagination.php`). Atribut itu dibaca sebagai komentar di PHP 7.4, jadi tetap aman.

## 2. Menempatkan source code

1. Ekstrak ZIP ke folder web server:
   * XAMPP: `C:\xampp\htdocs\Sistem-Manajemen-Surat`
   * Laragon: `C:\laragon\www\Sistem-Manajemen-Surat` (atau `D:\laragon\www\...`)
2. Struktur folder setelah diekstrak:

```
Sistem-Manajemen-Surat/
├── application/     ← kode aplikasi (controller, model, view, config)
├── assets/          ← CSS, JS, Bootstrap, Font Awesome, Chart.js
├── database/        ← db_persuratan_smk.sql
├── storage/         ← logo, tanda tangan, stempel, dan PDF arsip (terlindungi)
├── system/          ← inti CodeIgniter 3
├── vendor/          ← Dompdf (hasil Composer)
├── .htaccess
├── composer.json
└── index.php
```

3. Pastikan folder berikut dapat ditulisi oleh web server (di Windows biasanya otomatis):
   `storage/`, `storage/uploads/`, `storage/letters/`, `application/cache/`, `application/cache/sessions/`, `application/cache/dompdf/`, `application/logs/`.

## 3. Membuat database

**Cara A (otomatis):** file SQL sudah berisi `CREATE DATABASE IF NOT EXISTS db_persuratan_smk`, jadi langkah 4 langsung membuat database.

**Cara B (manual):** buka `http://localhost/phpmyadmin` → **New** → nama `db_persuratan_smk` → collation `utf8mb4_unicode_ci` → **Create**.

## 4. Mengimpor file SQL

1. Buka phpMyAdmin, lalu pilih database `db_persuratan_smk` (atau tidak perlu memilih apa pun jika memakai Cara A).
2. Buka tab **Import** → **Choose File** → pilih `database/db_persuratan_smk.sql` → **Import**.
3. Hasilnya 7 tabel: `users`, `school_settings`, `letter_types`, `letter_templates`, `letter_counters`, `letters`, `letter_files`. Isinya sudah termasuk 16 jenis surat dan satu akun admin contoh.

> Di hosting yang nama database-nya ditentukan cPanel (misalnya `user_persuratan`), hapus atau ubah dua baris `CREATE DATABASE ...` dan `USE ...` di bagian atas file SQL sebelum mengimpor.

Lewat command line:

```bash
mysql -u root -p < database/db_persuratan_smk.sql
```

## 5. Mengatur `application/config/config.php`

Bagian yang penting:

```php
$config['base_url']       = '';            // kosong = deteksi otomatis (lihat bagian 7)
$config['index_page']     = '';            // kosong karena memakai .htaccess
$config['encryption_key'] = 'GANTI-dengan-32-karakter-acak';
$config['language']       = 'indonesian';
$config['sess_save_path'] = APPPATH.'cache/sessions';
$config['csrf_protection']= TRUE;
$config['composer_autoload'] = FCPATH.'vendor/autoload.php';
```

**Wajib:** ganti `encryption_key` dengan string acak milik Anda sendiri. Contoh cara membuatnya:

```bash
php -r "echo bin2hex(random_bytes(16));"
```

Zona waktu diatur `Asia/Jakarta` di baris paling atas `config.php`. Untuk WITA/WIT, ubah menjadi `Asia/Makassar` atau `Asia/Jayapura`.

## 6. Mengatur `application/config/database.php`

```php
'hostname' => 'localhost',
'username' => 'root',                 // XAMPP/Laragon default
'password' => '',                     // XAMPP default kosong
'database' => 'db_persuratan_smk',
'dbdriver' => 'mysqli',
'char_set' => 'utf8mb4',
'dbcollat' => 'utf8mb4_unicode_ci',
```

Di hosting, isi sesuai user, password, dan nama database yang dibuat di cPanel.

## 7. Base URL & URL rewriting

* **Base URL:** jika `$config['base_url']` dibiarkan kosong, aplikasi mendeteksi alamatnya sendiri (misalnya `http://localhost/Sistem-Manajemen-Surat/` atau `http://sistem-manajemen-surat.test/` di Laragon). Untuk produksi sebaiknya diisi eksplisit:
  ```php
  $config['base_url'] = 'https://surat.namasekolah.sch.id/';
  ```
* **URL rewriting:** file `.htaccess` di root menghapus `index.php` dari URL sekaligus menolak akses langsung ke `application/`, `system/`, `vendor/`, `database/`, dan `storage/`.
  * Pastikan `mod_rewrite` aktif. Di XAMPP, buka `httpd.conf`, pastikan baris `LoadModule rewrite_module modules/mod_rewrite.so` tidak diawali `#`, lalu pastikan `AllowOverride All` berlaku untuk folder `htdocs`.
  * Jika muncul error 404/500 di subfolder, buka `.htaccess` lalu aktifkan `RewriteBase /Sistem-Manajemen-Surat/`.
  * Jika `mod_rewrite` sama sekali tidak tersedia, set `$config['index_page'] = 'index.php';`.

## 8. Memasang Dompdf

Dompdf **sudah termasuk** di folder `vendor/`, jadi tidak perlu langkah tambahan.

Bila ingin memasang ulang atau memperbarui lewat Composer:

```bash
cd Sistem-Manajemen-Surat
composer install --no-dev
```

`composer.json` mengunci platform ke PHP 7.4, sehingga versi yang terpasang tetap kompatibel dengan PHP 7.4 – 8.3. Dompdf dimuat melalui `$config['composer_autoload']`. Font dan cache Dompdf disimpan di `application/cache/dompdf/`.

## 9. Membuat akun administrator

Akun contoh setelah impor SQL:

| Username | Password |
|---|---|
| `admin` | `admin123` |

**Segera ganti password** lewat menu **Pengaturan Akun** setelah login pertama. Selama password bawaan masih dipakai, aplikasi menampilkan peringatan.

Membuat akun admin baru atau mereset password lewat terminal (perintah ini hanya bisa dijalankan dari CLI, tidak dari browser):

```bash
cd C:\xampp\htdocs\Sistem-Manajemen-Surat
C:\xampp\php\php.exe index.php tools create_admin tu_sekolah PasswordKuat123 "Nama Staf TU"
C:\xampp\php\php.exe index.php tools reset_password admin PasswordBaru123
```

Cara manual (phpMyAdmin): buat hash dengan
`php -r "echo password_hash('PasswordBaru123', PASSWORD_DEFAULT);"`, lalu jalankan:

```sql
INSERT INTO users (username, password_hash, full_name, role, is_active, created_at)
VALUES ('tu_sekolah', '<hasil hash>', 'Nama Staf TU', 'admin', 1, NOW());
```

Password **tidak pernah** disimpan sebagai teks biasa. Aplikasi memakai `password_hash()` (bcrypt) dan `password_verify()`.

## 10. Menjalankan & menguji aplikasi

1. Jalankan Apache dan MySQL dari XAMPP/Laragon.
2. Buka `http://localhost/Sistem-Manajemen-Surat/` (Laragon: `http://sistem-manajemen-surat.test/`).
3. Login sebagai `admin` / `admin123`.
4. Isi **Pengaturan Sekolah** terlebih dahulu: nama sekolah, kota, kepala sekolah, NIP, logo, dan format nomor. Aplikasi sengaja tidak mengisi data ini otomatis.

Skenario uji yang disarankan:

| # | Alur | Cara uji |
|---|---|---|
| 1 | Login ke dashboard | Login → kartu statistik & grafik tampil |
| 2 | Membuat surat keterangan siswa aktif | Buat Surat → Surat Keterangan Siswa Aktif |
| 3 | Menyimpan ke database | Simpan & Finalkan → halaman detail menampilkan nomor |
| 4 | Pratinjau | Tombol **Pratinjau** (membuka tab baru sebelum disimpan) |
| 5 | Unduh PDF A4 | Detail → **Unduh PDF** |
| 6 | Buka dari arsip | Arsip Surat → ikon mata |
| 7 | Edit surat | Detail → **Edit** → simpan |
| 8 | Cari nomor/perihal | Arsip → kolom cari |
| 9 | Filter jenis & tahun | Arsip → pilih jenis, bulan, tahun → Filter |
| 10 | Hapus dengan konfirmasi | Ikon tempat sampah → muncul dialog konfirmasi |
| 11 | Identitas sekolah di PDF | Ubah nama sekolah → unduh ulang PDF |
| 12 | Logout & proteksi | Logout → buka `/dashboard` → dialihkan ke login |

Seluruh 12 alur di atas sudah diuji secara otomatis (permintaan HTTP sungguhan, Apache + MariaDB + PHP 8.3) sebelum paket ini diserahkan, termasuk uji PDF untuk ke-16 jenis surat.

## 11. Memasang di shared hosting

1. Buat database + user di cPanel (**MySQL Databases**), lalu beri user tersebut hak **ALL PRIVILEGES**.
2. Impor SQL lewat phpMyAdmin cPanel (hapus dulu baris `CREATE DATABASE`/`USE`, lihat bagian 4).
3. Unggah ZIP ke `public_html/` (atau subdomain/subfolder), lalu **Extract** di File Manager.
4. Sesuaikan `application/config/database.php` dan isi `base_url` di `config.php`, misalnya `https://surat.sekolah.sch.id/`.
5. Aktifkan mode produksi dengan membuka `.htaccess` dan menghapus tanda `#` pada `SetEnv CI_ENV production`, supaya pesan error tidak tampil ke pengguna. Jika hosting tidak mendukung `SetEnv`, ubah langsung di `index.php`: `define('ENVIRONMENT', 'production');`.
6. Pastikan izin folder `storage/` dan `application/cache/` adalah **755** (file **644**).
7. Pilih PHP 7.4 – 8.3 di **Select PHP Version** / **MultiPHP Manager**, lalu aktifkan ekstensi `gd`, `mbstring`, `fileinfo`, `mysqli`.
8. Gunakan HTTPS. Setelah HTTPS aktif, Anda bisa mengatur `$config['cookie_secure'] = TRUE;` di `config.php`.
9. Login, ganti password admin, lalu lengkapi Pengaturan Sekolah.

## 12. Backup database & file surat

Lakukan backup **kedua** bagian berikut secara rutin (disarankan mingguan):

1. **Database:** phpMyAdmin → pilih `db_persuratan_smk` → **Export** → Quick/SQL → **Go**. Atau lewat CLI:
   ```bash
   mysqldump -u root -p --single-transaction db_persuratan_smk > backup_surat_2026-10-27.sql
   ```
2. **File:** salin seluruh folder `storage/` (berisi logo, tanda tangan, stempel, dan PDF arsip di `storage/letters/TAHUN/BULAN/`).

Pemulihan: impor file `.sql`, lalu kembalikan folder `storage/`. Data surat ada di database, jadi PDF tetap bisa dibuat ulang lewat tombol **Buat Ulang PDF** meskipun file PDF arsip hilang.

---

## 13. Arsitektur aplikasi

Pola **MVC CodeIgniter 3**. Logika bisnis ditempatkan di model dan library, sedangkan view hanya menampilkan data.

```
application/
├── config/
│   ├── letter_templates.php   ← REGISTRY 16 template surat (field formulir per jenis)
│   ├── routes.php             ← URL ramah: /surat, /arsip, /pengaturan, ...
│   └── config.php, database.php, autoload.php
├── core/MY_Controller.php     ← MY_Controller (pengaturan sekolah, layout) & Admin_Controller (wajib login)
├── controllers/
│   ├── Auth.php               ← login, logout (POST + CSRF)
│   ├── Dashboard.php          ← statistik & grafik dari database
│   ├── Letters.php            ← pilih jenis, form, pratinjau, simpan, detail, edit, hapus, PDF
│   ├── Archive.php            ← arsip: cari, filter jenis/bulan/tahun, pagination, unduh PDF arsip
│   ├── Letter_types.php       ← master jenis surat + teks pembuka/isi/penutup
│   ├── Settings.php           ← identitas sekolah, kop, penandatangan, format nomor, unggah gambar
│   ├── Account.php            ← profil & ganti password
│   ├── Media.php              ← menyajikan logo/ttd/stempel dari storage (terlindungi)
│   └── Tools.php              ← perintah CLI: create_admin, reset_password
├── models/                    ← User_model, Settings_model, Letter_type_model, Letter_model, Letter_file_model
├── libraries/
│   ├── Template_registry.php  ← membaca registry, validasi & sanitasi field dinamis, placeholder
│   ├── Letter_numbering.php   ← penomoran atomik (SELECT ... FOR UPDATE), format token
│   └── Letter_renderer.php    ← menyusun HTML surat & mengubahnya menjadi PDF lewat Dompdf
├── helpers/app_helper.php     ← e() escape, tgl_indo(), bulan_romawi(), tabel surat, dll.
├── language/indonesian/       ← pesan validasi & upload berbahasa Indonesia
└── views/
    ├── layouts/app.php        ← sidebar + navbar + modal konfirmasi
    ├── auth/login.php, dashboard/index.php, archive/index.php, settings/index.php, account/index.php
    ├── letter_types/index.php, form.php
    └── letters/
        ├── index.php, select_type.php, create.php, edit.php, detail.php, preview.php
        ├── _form.php, _field.php, _table.php
        ├── pdf/document.php   ← kerangka surat A4: kop, kepala surat, tanda tangan
        └── templates/*.php    ← isi khusus 16 jenis surat
```

**Alur pembuatan surat:** pilih jenis → formulir dibangun dari registry → isi → **Pratinjau** (HTML A4 di tab baru, nomor masih perkiraan) → **Simpan Draft** atau **Simpan & Finalkan** → nomor dialokasikan dalam transaksi → data masuk ke `letters` → saat difinalkan, PDF arsip disimpan di `storage/letters/` → PDF selalu bisa diunduh atau dibuat ulang.

**Strategi PDF (bagian H):** sumber kebenarannya adalah **data di database**. PDF dibuat *on-demand* setiap kali diunduh, jadi selalu bisa dibuat ulang. Selain itu, saat surat difinalkan atau diedit, satu salinan PDF disimpan sebagai file di `storage/letters/YYYY/MM/` dan path-nya dicatat di `letter_files` (beserta ukuran dan checksum SHA-256). PDF **tidak** disimpan sebagai Base64 di database. Folder `storage/` ditolak oleh `.htaccess`, dan file hanya bisa diunduh melalui controller yang mewajibkan login.

**Draft vs Final:** PDF surat berstatus Draft diberi watermark "DRAFT". Surat Final dicetak bersih.

## 14. Rancangan database

| Tabel | Fungsi | Relasi |
|---|---|---|
| `users` | akun admin/TU, hash password, penguncian login | — |
| `school_settings` | satu baris (id=1): identitas, kop, penandatangan, format nomor, path gambar | — |
| `letter_types` | master jenis surat, `code` unik, `template_key` | 1 → N `letters` (RESTRICT) |
| `letter_templates` | teks pembuka/isi/penutup per jenis (dapat diedit) | 1 → 1 `letter_types` (CASCADE) |
| `letter_counters` | nomor urut terakhir per *scope* + periode | — |
| `letters` | data surat: nomor (UNIQUE), tanggal, perihal, tujuan, penandatangan, `content_data` JSON, status | N → 1 `letter_types`, `users` |
| `letter_files` | metadata PDF arsip (path relatif, ukuran, checksum) | N → 1 `letters` (CASCADE) |

**Indeks:** `letters.letter_number` UNIQUE, `(letter_type_id, letter_date)`, `letter_date`, `number_year`, `subject(100)`, `status`. Filter bulan/tahun memakai rentang tanggal (`letter_date BETWEEN ...`) supaya indeks tetap terpakai.

**Mengapa kombinasi kolom terstruktur + JSON?**

* Kolom yang dipakai untuk mencari, memfilter, dan menampilkan arsip (nomor, tanggal, perihal, tujuan, jenis, status, penandatangan) dibuat sebagai **kolom biasa** supaya bisa diindeks dan diquery dengan efisien.
* Field yang **berbeda-beda per jenis surat** (data siswa, daftar petugas, jadwal rapat, dan sebagainya) disimpan di kolom `content_data` bertipe **JSON**. Dengan begitu, menambah jenis surat baru tidak memerlukan perubahan skema maupun puluhan kolom kosong. Tipe `JSON` didukung MySQL 5.7+ dan MariaDB 10.2+ (di MariaDB disimpan sebagai LONGTEXT dengan validasi `JSON_VALID`).
* Seluruh field JSON tetap divalidasi di server berdasarkan definisinya di `config/letter_templates.php` (wajib/tidak, panjang maksimal, format tanggal, pilihan *select*), lalu di-escape saat ditampilkan.

## 15. Penomoran surat

Diatur di **Pengaturan Sekolah → Format Penomoran Surat**.

| Token | Arti | Contoh |
|---|---|---|
| `{NO}` | nomor urut dengan nol di depan (jumlah digit dapat diatur) | `007` |
| `{NO_RAW}` | nomor urut tanpa nol | `7` |
| `{KODE}` | kode jenis surat (menu Jenis Surat) | `SKA` |
| `{SEKOLAH}` | kode singkat sekolah | `SMKN1` |
| `{NPSN}` | NPSN | `2010xxxx` |
| `{BULAN}` / `{BULAN_ROMAWI}` | bulan surat | `10` / `X` |
| `{TAHUN}` | tahun surat (atau "Tahun Surat" tetap dari pengaturan) | `2026` |

Contoh format: `{NO}/{KODE}/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}` menghasilkan `007/SKA/SMKN1/X/2026`. Jika sekolah memakai kode klasifikasi arsip (misalnya `421.5`), isikan kode itu sebagai **Kode** di menu Jenis Surat atau tulis langsung di format.

Pilihan yang tersedia:

* **Diulang:** setiap tahun, setiap bulan, atau tidak pernah.
* **Urutan:** satu urutan untuk semua jenis, atau terpisah per jenis surat (kategori).
* **Nomor manual:** bisa dipilih per surat. Sistem menolak nomor yang sudah dipakai.

Jaminan anti-duplikat:

1. Kolom `letters.letter_number` bersifat **UNIQUE** di database.
2. Nomor dialokasikan di dalam **transaksi** dengan `SELECT ... FOR UPDATE` pada `letter_counters`, sehingga dua staf yang menyimpan bersamaan tidak akan mendapat nomor yang sama.
3. Penghitung **tidak pernah mundur**. Nomor milik surat yang sudah dihapus tidak dipakai ulang secara otomatis, dan nomor yang sudah diisi manual akan dilewati.

## 16. Menambah template surat (developer)

1. Tambahkan entri baru di `application/config/letter_templates.php`:
   ```php
   'surat_dispensasi' => array(
       'label' => 'Surat Dispensasi', 'layout' => 'keterangan', 'title' => 'SURAT DISPENSASI',
       'default_subject' => 'Dispensasi', 'recipient' => 'hidden', 'recipient_field' => 'nama_siswa',
       'attachment' => FALSE,
       'fields' => array(
           'nama_siswa' => array('label' => 'Nama Siswa', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
           'kelas'      => array('label' => 'Kelas', 'type' => 'text', 'required' => TRUE, 'max' => 30, 'col' => 6),
           'tanggal'    => array('label' => 'Tanggal', 'type' => 'date', 'format' => 'day_date', 'required' => TRUE, 'col' => 6),
       ),
   ),
   ```
   Tipe field yang tersedia: `text`, `textarea`, `date`, `time`, `number`, `select` (dengan `options`), dan `rows` (tabel dinamis dengan `columns`).
2. Buat view isi surat `application/views/letters/templates/surat_dispensasi.php` (salin dari template yang mirip). Variabel yang tersedia: `$t` (teks pembuka/isi/penutup, sudah aman), `$v` (nilai field yang sudah diformat), `$d` (data mentah), `$letter`, `$school`, dan `$tpl`. Helper yang bisa dipakai: `letter_data_table()`, `letter_grid()`, `letter_list()`, `letter_paragraph()`.
3. Buka menu **Jenis Surat → Tambah**, pilih template baru, lalu isi teks pembuka/penutup. Placeholder `{nama_siswa}` dan sejenisnya akan diganti otomatis.

Kop surat, blok Nomor/Perihal/Kepada, dan tanda tangan diatur terpusat di `views/letters/pdf/document.php`.

## 17. Keamanan

* Password di-hash dengan `password_hash()` (bcrypt) dan di-*rehash* otomatis jika algoritma berubah. Login dikunci 10 menit setelah 5 kali gagal, dan ID sesi diganti saat login atau ganti password.
* Semua halaman (kecuali login) memakai `Admin_Controller`, yang memeriksa sesi dan status aktif akun di setiap request. Header `Cache-Control: no-store` mencegah halaman terlihat lewat tombol Back setelah logout.
* **CSRF** CodeIgniter aktif di semua formulir POST. Aksi yang mengubah data (hapus, logout, buat ulang PDF, hapus gambar) **hanya menerima POST**.
* **SQL injection:** seluruh query memakai Query Builder atau query binding (`?`).
* **XSS:** semua output di-escape dengan `e()` (`htmlspecialchars`). Teks template di-escape dulu, baru placeholder diganti.
* **Validasi server** untuk semua formulir (form_validation dan validasi field dinamis), tidak mengandalkan JavaScript.
* **Upload:** hanya PNG/JPG maksimal 5 MB, MIME diperiksa ulang dengan `getimagesize()`, nama file diacak (`encrypt_name`), dan file disimpan di `storage/` yang tertutup untuk akses langsung.
* **Dompdf** dijalankan dengan `isRemoteEnabled=false`, `isPhpEnabled=false`, dan `chroot` ke folder aplikasi. Gambar disisipkan sebagai data URI.
* Error dan aktivitas penting (login gagal, hapus surat, kegagalan PDF) dicatat di `application/logs/`.
* Tanda tangan dan stempel **tidak pernah dibuat oleh aplikasi**. Jika gambar resmi tidak diunggah, PDF menyediakan ruang kosong untuk tanda tangan basah.

## 18. Pemecahan masalah

| Gejala | Solusi |
|---|---|
| 404 di semua halaman selain beranda | Aktifkan `mod_rewrite` dan `AllowOverride All`, atau isi `RewriteBase` di `.htaccess` |
| "The action you have requested is not allowed" | Token CSRF kedaluwarsa: muat ulang halaman lalu kirim lagi. Pastikan cookie tidak diblokir |
| Login berhasil tetapi kembali ke halaman login | Folder `application/cache/sessions` tidak dapat ditulisi, atau `base_url` tidak sesuai domain |
| PDF gagal / halaman putih | Aktifkan ekstensi `gd`, `mbstring`, `dom`. Pastikan `application/cache/dompdf` dapat ditulisi. Periksa `application/logs/` |
| Logo tidak tampil di PDF | Gunakan PNG/JPG dan pastikan ekstensi `gd` aktif |
| Error tipe `JSON` saat impor SQL | Versi MySQL < 5.7 atau MariaDB < 10.2. Perbarui XAMPP/server database |
| Pesan "Deprecated" di PHP 8.2+ | Pastikan memakai folder `system/` dari paket ini (sudah ditambal), atau atur mode produksi |
| Upload gagal "Folder unggahan tidak dapat ditulisi" | Atur izin `storage/uploads` menjadi 755 |

## 19. Permohonan surat oleh siswa (tanpa login)

Siswa dapat meminta surat sendiri lewat formulir publik di `http://alamat-aplikasi/permohonan` (tautan juga tersedia di halaman login). Siswa tidak perlu akun.

**Alur:** siswa memilih jenis surat lalu mengisi data dan nomor WhatsApp, kemudian mendapat **kode permohonan** 10 karakter. TU membuka menu **Permohonan Siswa** dan memeriksa data. Ada dua kemungkinan:

* **Proses Jadi Surat**: formulir surat terisi otomatis dari data siswa. TU memeriksa, memperbaiki bila perlu, lalu menyimpan. Nomor surat terbit dan status permohonan berubah menjadi **Siap Diambil**. Setelah surat diserahkan, klik **Tandai Sudah Diambil**.
* **Tolak**: TU wajib menulis alasan, dan alasan ini terlihat oleh siswa saat mengecek status.

Siswa mengecek status di `/permohonan/cek` dengan kode tersebut. Nama pemohon di halaman status disamarkan.

**Jenis surat yang bisa diajukan siswa** diatur di menu Jenis Surat lewat sakelar "Siswa dapat mengajukan sendiri". Bawaannya: Surat Keterangan Siswa Aktif, Surat Keterangan Lulus, dan Surat Keterangan Telah Melaksanakan PKL.

**Perlindungan formulir publik:** token CSRF, kolom jebakan robot (honeypot), soal hitungan sederhana, maksimal 20 permohonan per jam (60 per hari) per IP (batas ada di `application/controllers/Permohonan.php`), dan maksimal 3 permohonan menunggu per nomor HP. Seluruh isian divalidasi di server. Surat tidak pernah bisa diunduh dari portal publik; siswa mengambilnya di ruang TU.

> Karena siswa tidak login dan tidak ada data induk, data yang masuk **belum terverifikasi**. TU wajib mencocokkannya dengan buku induk sebelum menerbitkan surat.

**Pembaruan dari versi sebelumnya:** struktur database diperbarui otomatis saat aplikasi pertama kali dibuka (tabel `letter_requests` dan kolom `letter_types.allow_public`). Bila user database tidak punya hak `CREATE`/`ALTER` (misalnya di sebagian hosting), impor `database/update_permohonan_siswa.sql` lewat phpMyAdmin.

---

Fitur yang belum termasuk di versi dasar (dapat dikembangkan setelah stabil): disposisi berjenjang, surat masuk, multi-peran, tanda tangan elektronik, notifikasi WhatsApp/email.
