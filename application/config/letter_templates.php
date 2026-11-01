<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| =============================================================================
| REGISTRY TEMPLATE SURAT
| =============================================================================
| Setiap jenis surat (tabel `letter_types`) menunjuk ke salah satu kunci
| template di bawah ini melalui kolom `template_key`.
|
| CARA MENAMBAH TEMPLATE BARU (untuk developer):
|   1. Tambahkan entri baru di array $config['letter_templates'] (kunci unik,
|      huruf kecil + garis bawah).
|   2. Buat file view isi surat: application/views/letters/templates/<kunci>.php
|      (contoh lihat file template yang sudah ada).
|   3. Tambahkan jenis surat baru melalui menu "Jenis Surat" dan pilih template
|      tersebut. Teks pembuka/isi/penutup dapat diedit dari menu yang sama.
|
| Struktur setiap template:
|   label        : nama template (tampil di menu Jenis Surat)
|   layout       : 'keterangan' -> judul di tengah + nomor di bawah judul
|                  'dinas'      -> blok Nomor/Lampiran/Perihal + Kepada Yth.
|   title        : judul surat (khusus layout keterangan)
|   default_subject : perihal bawaan
|   recipient    : 'required' | 'optional' | 'hidden'  (kolom Kepada/Tujuan)
|   recipient_field : (opsional) kunci field yang otomatis dipakai sebagai
|                  penerima/tujuan di arsip jika kolom tujuan tidak diisi
|   attachment   : TRUE jika kolom Lampiran ditampilkan
|   guide        : teks bantuan pada tombol (!) di menu Buat Surat
|                  ['purpose' => kegunaan, 'when' => [contoh pemakaian], 'tip' => tips]
|   fields       : daftar field khusus jenis surat (disimpan sebagai JSON)
|       type     : text | textarea | date | time | number | select | rows
|       label, required, max, placeholder, help, col (lebar kolom Bootstrap 1-12)
|       options  : untuk select (array nilai)
|       format   : 'day_date' untuk tanggal dengan nama hari
|       columns  : untuk rows (tabel dinamis) => [kunci => label]
|
| Placeholder yang dapat dipakai pada teks pembuka/isi/penutup:
|   {nama_sekolah} {alamat_sekolah} {kota} {jabatan_penandatangan}
|   {nama_penandatangan} {perihal} {tujuan} serta {kunci_field} apa pun.
| =============================================================================
*/

$_jk    = array('Laki-laki', 'Perempuan');
$_siswa = array(
	'nama_siswa'          => array('label' => 'Nama Siswa', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
	'nis'                 => array('label' => 'NIS', 'type' => 'text', 'required' => TRUE, 'max' => 30, 'col' => 3),
	'nisn'                => array('label' => 'NISN', 'type' => 'text', 'required' => FALSE, 'max' => 20, 'col' => 3),
);
$_ttl = array(
	'tempat_lahir'        => array('label' => 'Tempat Lahir', 'type' => 'text', 'required' => TRUE, 'max' => 100, 'col' => 6),
	'tanggal_lahir'       => array('label' => 'Tanggal Lahir', 'type' => 'date', 'required' => TRUE, 'col' => 6),
);

$config['letter_templates'] = array(

	// 1 -------------------------------------------------------------------
	'siswa_aktif' => array(
		'guide'           => array(
			'purpose' => 'Menerangkan secara resmi bahwa seorang siswa masih terdaftar dan aktif belajar di sekolah.',
			'when'    => array('Persyaratan beasiswa (PIP, KIP, beasiswa daerah/swasta)', 'Tunjangan anak bagi orang tua ASN/TNI/Polri/karyawan', 'Pembukaan rekening, BPJS, atau administrasi bank', 'Persyaratan lomba atau kegiatan di luar sekolah'),
			'tip'     => 'Isi kolom Keperluan tanpa kata "untuk", misalnya: persyaratan pengajuan beasiswa.',
		),
		'label'           => 'Surat Keterangan Siswa Aktif',
		'layout'          => 'keterangan',
		'title'           => 'SURAT KETERANGAN AKTIF',
		'default_subject' => 'Keterangan Siswa Aktif',
		'recipient'       => 'hidden',
		'recipient_field' => 'nama_siswa',
		'attachment'      => FALSE,
		'fields'          => $_siswa + $_ttl + array(
			'jenis_kelamin'       => array('label' => 'Jenis Kelamin', 'type' => 'select', 'options' => $_jk, 'required' => TRUE, 'col' => 4),
			'kelas'               => array('label' => 'Kelas', 'type' => 'text', 'required' => TRUE, 'max' => 30, 'col' => 4, 'placeholder' => 'Contoh: XI TKJ 1'),
			'tahun_pelajaran'     => array('label' => 'Tahun Pelajaran', 'type' => 'text', 'required' => TRUE, 'max' => 20, 'col' => 4, 'placeholder' => 'Contoh: 2026/2027'),
			'kompetensi_keahlian' => array('label' => 'Konsentrasi/Kompetensi Keahlian', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
			'nama_orang_tua'      => array('label' => 'Nama Orang Tua/Wali', 'type' => 'text', 'required' => FALSE, 'max' => 150, 'col' => 6),
			'alamat'              => array('label' => 'Alamat Siswa', 'type' => 'textarea', 'required' => FALSE, 'max' => 500, 'col' => 12),
			'keperluan'           => array('label' => 'Keperluan', 'type' => 'text', 'required' => TRUE, 'max' => 255, 'col' => 12, 'placeholder' => 'Contoh: persyaratan pengajuan beasiswa'),
		),
	),

	// 2 -------------------------------------------------------------------
	'lulus' => array(
		'guide'           => array(
			'purpose' => 'Menerangkan bahwa siswa telah menyelesaikan pendidikan dan dinyatakan lulus, biasanya sebelum ijazah terbit atau sebagai pengganti sementara.',
			'when'    => array('Mendaftar kuliah atau kerja sebelum ijazah dibagikan', 'Pengganti sementara ijazah yang sedang diproses', 'Persyaratan seleksi beasiswa lanjutan'),
			'tip'     => 'Nomor ijazah boleh dikosongkan bila ijazah belum terbit.',
		),
		'label'           => 'Surat Keterangan Lulus',
		'layout'          => 'keterangan',
		'title'           => 'SURAT KETERANGAN LULUS',
		'default_subject' => 'Keterangan Lulus',
		'recipient'       => 'hidden',
		'recipient_field' => 'nama_siswa',
		'attachment'      => FALSE,
		'fields'          => $_siswa + $_ttl + array(
			'nama_orang_tua'      => array('label' => 'Nama Orang Tua/Wali', 'type' => 'text', 'required' => FALSE, 'max' => 150, 'col' => 6),
			'kompetensi_keahlian' => array('label' => 'Konsentrasi/Kompetensi Keahlian', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
			'tahun_pelajaran'     => array('label' => 'Tahun Pelajaran', 'type' => 'text', 'required' => TRUE, 'max' => 20, 'col' => 4, 'placeholder' => 'Contoh: 2025/2026'),
			'tanggal_kelulusan'   => array('label' => 'Tanggal Kelulusan', 'type' => 'date', 'required' => TRUE, 'col' => 4),
			'nomor_ijazah'        => array('label' => 'Nomor Ijazah', 'type' => 'text', 'required' => FALSE, 'max' => 60, 'col' => 4, 'help' => 'Kosongkan jika ijazah belum terbit.'),
			'keperluan'           => array('label' => 'Keperluan', 'type' => 'text', 'required' => FALSE, 'max' => 255, 'col' => 12),
		),
	),

	// 3 -------------------------------------------------------------------
	'pindah' => array(
		'guide'           => array(
			'purpose' => 'Menerangkan bahwa siswa pindah ke sekolah lain beserta tanggal berlakunya, sebagai dokumen mutasi keluar.',
			'when'    => array('Orang tua mengajukan mutasi siswa ke sekolah lain', 'Siswa pindah domisili mengikuti orang tua'),
			'tip'     => 'Tanggal efektif adalah hari terakhir hak dan kewajiban siswa di sekolah ini.',
		),
		'label'           => 'Surat Keterangan Pindah Sekolah',
		'layout'          => 'keterangan',
		'title'           => 'SURAT KETERANGAN PINDAH SEKOLAH',
		'default_subject' => 'Keterangan Pindah Sekolah',
		'recipient'       => 'hidden',
		'recipient_field' => 'nama_siswa',
		'attachment'      => FALSE,
		'fields'          => $_siswa + $_ttl + array(
			'jenis_kelamin'         => array('label' => 'Jenis Kelamin', 'type' => 'select', 'options' => $_jk, 'required' => TRUE, 'col' => 4),
			'kelas'                 => array('label' => 'Kelas', 'type' => 'text', 'required' => TRUE, 'max' => 30, 'col' => 4),
			'kompetensi_keahlian'   => array('label' => 'Konsentrasi/Kompetensi Keahlian', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 4),
			'nama_orang_tua'        => array('label' => 'Nama Orang Tua/Wali', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
			'alamat'                => array('label' => 'Alamat', 'type' => 'text', 'required' => FALSE, 'max' => 255, 'col' => 6),
			'sekolah_tujuan'        => array('label' => 'Sekolah Tujuan', 'type' => 'text', 'required' => TRUE, 'max' => 200, 'col' => 6),
			'alamat_sekolah_tujuan' => array('label' => 'Alamat Sekolah Tujuan', 'type' => 'text', 'required' => FALSE, 'max' => 255, 'col' => 6),
			'alasan_pindah'         => array('label' => 'Alasan Pindah', 'type' => 'text', 'required' => TRUE, 'max' => 255, 'col' => 8),
			'tanggal_efektif'       => array('label' => 'Terhitung Mulai Tanggal', 'type' => 'date', 'required' => TRUE, 'col' => 4),
		),
	),

	// 4 -------------------------------------------------------------------
	'panggilan_ortu' => array(
		'guide'           => array(
			'purpose' => 'Mengundang orang tua/wali datang ke sekolah untuk membicarakan hal tertentu tentang putra/putrinya.',
			'when'    => array('Konsultasi perkembangan belajar atau kehadiran siswa', 'Pembinaan kedisiplinan oleh BK atau wali kelas', 'Pembahasan administrasi atau keuangan siswa'),
			'tip'     => 'Hari otomatis tercetak dari tanggal pertemuan, misalnya "Senin, 2 November 2026".',
		),
		'label'           => 'Surat Panggilan Orang Tua/Wali',
		'layout'          => 'dinas',
		'default_subject' => 'Panggilan Orang Tua/Wali Siswa',
		'recipient'       => 'required',
		'default_recipient' => 'Bapak/Ibu Orang Tua/Wali Siswa',
		'attachment'      => FALSE,
		'fields'          => array(
			'nama_siswa'   => array('label' => 'Nama Siswa', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
			'kelas'        => array('label' => 'Kelas', 'type' => 'text', 'required' => TRUE, 'max' => 30, 'col' => 3),
			'nis'          => array('label' => 'NIS', 'type' => 'text', 'required' => FALSE, 'max' => 30, 'col' => 3),
			'hari_tanggal' => array('label' => 'Hari/Tanggal Pertemuan', 'type' => 'date', 'format' => 'day_date', 'required' => TRUE, 'col' => 4),
			'waktu'        => array('label' => 'Waktu', 'type' => 'text', 'required' => TRUE, 'max' => 60, 'col' => 4, 'placeholder' => 'Contoh: 09.00 WIB s.d. selesai'),
			'tempat'       => array('label' => 'Tempat', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 4, 'placeholder' => 'Contoh: Ruang BK'),
			'keperluan'    => array('label' => 'Keperluan', 'type' => 'textarea', 'required' => TRUE, 'max' => 1000, 'col' => 8),
			'menemui'      => array('label' => 'Menemui', 'type' => 'text', 'required' => FALSE, 'max' => 150, 'col' => 4, 'placeholder' => 'Contoh: Wali Kelas / Guru BK'),
		),
	),

	// 5 -------------------------------------------------------------------
	'undangan_rapat' => array(
		'guide'           => array(
			'purpose' => 'Mengundang pihak terkait menghadiri rapat beserta waktu, tempat, dan agendanya.',
			'when'    => array('Rapat dewan guru atau staf', 'Rapat komite sekolah atau orang tua', 'Rapat koordinasi dengan pihak luar'),
			'tip'     => 'Tulis satu agenda per baris pada kolom Acara agar tercetak bernomor.',
		),
		'label'           => 'Surat Undangan Rapat',
		'layout'          => 'dinas',
		'default_subject' => 'Undangan Rapat',
		'recipient'       => 'required',
		'attachment'      => TRUE,
		'fields'          => array(
			'hari_tanggal' => array('label' => 'Hari/Tanggal Rapat', 'type' => 'date', 'format' => 'day_date', 'required' => TRUE, 'col' => 4),
			'waktu'        => array('label' => 'Waktu', 'type' => 'text', 'required' => TRUE, 'max' => 60, 'col' => 4, 'placeholder' => 'Contoh: 13.00 WIB s.d. selesai'),
			'tempat'       => array('label' => 'Tempat', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 4),
			'acara'        => array('label' => 'Acara/Agenda', 'type' => 'textarea', 'required' => TRUE, 'max' => 1500, 'col' => 12, 'help' => 'Satu agenda per baris.'),
			'catatan'      => array('label' => 'Catatan', 'type' => 'textarea', 'required' => FALSE, 'max' => 500, 'col' => 12),
		),
	),

	// 6 -------------------------------------------------------------------
	'permohonan' => array(
		'guide'           => array(
			'purpose' => 'Surat dinas umum untuk mengajukan permintaan resmi kepada instansi atau pihak lain.',
			'when'    => array('Permohonan izin penggunaan tempat atau fasilitas', 'Permohonan narasumber atau bantuan', 'Permohonan data atau rekomendasi ke dinas'),
			'tip'     => 'Gunakan kolom Rincian untuk daftar butir permohonan, satu butir per baris.',
		),
		'label'           => 'Surat Permohonan',
		'layout'          => 'dinas',
		'default_subject' => 'Permohonan',
		'recipient'       => 'required',
		'attachment'      => TRUE,
		'fields'          => array(
			'isi_permohonan' => array('label' => 'Isi Permohonan', 'type' => 'textarea', 'required' => TRUE, 'max' => 4000, 'col' => 12, 'rows' => 6),
			'rincian'        => array('label' => 'Rincian (opsional)', 'type' => 'textarea', 'required' => FALSE, 'max' => 2000, 'col' => 12, 'help' => 'Satu butir per baris; akan ditampilkan sebagai daftar bernomor.'),
		),
	),

	// 7 -------------------------------------------------------------------
	'pemberitahuan' => array(
		'guide'           => array(
			'purpose' => 'Menyampaikan informasi resmi kepada pihak tertentu, misalnya orang tua, guru, atau instansi lain.',
			'when'    => array('Pemberitahuan libur atau perubahan jadwal belajar', 'Pemberitahuan kegiatan sekolah', 'Informasi pembayaran atau administrasi'),
			'tip'     => 'Hari/tanggal, waktu, dan tempat diisi hanya bila pemberitahuan menyangkut kegiatan.',
		),
		'label'           => 'Surat Pemberitahuan',
		'layout'          => 'dinas',
		'default_subject' => 'Pemberitahuan',
		'recipient'       => 'required',
		'attachment'      => TRUE,
		'fields'          => array(
			'isi_pemberitahuan' => array('label' => 'Isi Pemberitahuan', 'type' => 'textarea', 'required' => TRUE, 'max' => 4000, 'col' => 12, 'rows' => 6),
			'hari_tanggal'      => array('label' => 'Hari/Tanggal Kegiatan (opsional)', 'type' => 'date', 'format' => 'day_date', 'required' => FALSE, 'col' => 4),
			'waktu'             => array('label' => 'Waktu (opsional)', 'type' => 'text', 'required' => FALSE, 'max' => 60, 'col' => 4),
			'tempat'            => array('label' => 'Tempat (opsional)', 'type' => 'text', 'required' => FALSE, 'max' => 150, 'col' => 4),
		),
	),

	// 8 -------------------------------------------------------------------
	'tugas' => array(
		'guide'           => array(
			'purpose' => 'Memberi tugas resmi kepada satu atau beberapa guru/pegawai untuk melaksanakan kegiatan tertentu.',
			'when'    => array('Mengikuti pelatihan, diklat, atau workshop', 'Mendampingi siswa lomba atau kunjungan industri', 'Menjadi panitia atau mewakili sekolah dalam acara dinas'),
			'tip'     => 'Satu surat bisa memuat banyak petugas; tambahkan baris pada tabel petugas.',
		),
		'label'           => 'Surat Tugas Guru/Pegawai',
		'layout'          => 'keterangan',
		'title'           => 'SURAT TUGAS',
		'default_subject' => 'Surat Tugas',
		'recipient'       => 'hidden',
		'attachment'      => FALSE,
		'fields'          => array(
			'dasar'           => array('label' => 'Dasar (opsional)', 'type' => 'textarea', 'required' => FALSE, 'max' => 1500, 'col' => 12, 'help' => 'Satu dasar per baris, misalnya surat undangan dari instansi lain.'),
			'petugas'         => array('label' => 'Guru/Pegawai yang Ditugaskan', 'type' => 'rows', 'required' => TRUE, 'col' => 12,
				'columns' => array('nama' => 'Nama', 'nip' => 'NIP/NUPTK', 'jabatan' => 'Jabatan')),
			'uraian_tugas'    => array('label' => 'Untuk (Uraian Tugas)', 'type' => 'textarea', 'required' => TRUE, 'max' => 1500, 'col' => 12),
			'tanggal_mulai'   => array('label' => 'Tanggal Mulai', 'type' => 'date', 'format' => 'day_date', 'required' => TRUE, 'col' => 4),
			'tanggal_selesai' => array('label' => 'Tanggal Selesai', 'type' => 'date', 'format' => 'day_date', 'required' => FALSE, 'col' => 4),
			'tempat'          => array('label' => 'Tempat', 'type' => 'text', 'required' => TRUE, 'max' => 200, 'col' => 4),
		),
	),

	// 9 -------------------------------------------------------------------
	'pengantar_pkl' => array(
		'guide'           => array(
			'purpose' => 'Mengantar siswa yang akan melaksanakan Praktik Kerja Lapangan (PKL) ke perusahaan atau instansi tempat PKL.',
			'when'    => array('Penyerahan siswa PKL ke DU-DI pada awal periode PKL', 'Pengajuan siswa ke instansi yang sudah menyatakan bersedia menerima'),
			'tip'     => 'Satu surat untuk satu instansi; masukkan semua siswa yang PKL di instansi itu.',
		),
		'label'           => 'Surat Pengantar PKL',
		'layout'          => 'dinas',
		'default_subject' => 'Pengantar Praktik Kerja Lapangan (PKL)',
		'recipient'       => 'required',
		'default_recipient' => 'Pimpinan',
		'attachment'      => TRUE,
		'fields'          => array(
			'nama_instansi'     => array('label' => 'Nama Instansi/DU-DI Tempat PKL', 'type' => 'text', 'required' => TRUE, 'max' => 200, 'col' => 12),
			'siswa'             => array('label' => 'Daftar Siswa PKL', 'type' => 'rows', 'required' => TRUE, 'col' => 12,
				'columns' => array('nama' => 'Nama Siswa', 'nis' => 'NIS', 'kompetensi' => 'Konsentrasi Keahlian')),
			'tanggal_mulai'     => array('label' => 'Tanggal Mulai PKL', 'type' => 'date', 'required' => TRUE, 'col' => 3),
			'tanggal_selesai'   => array('label' => 'Tanggal Selesai PKL', 'type' => 'date', 'required' => TRUE, 'col' => 3),
			'guru_pembimbing'   => array('label' => 'Guru Pembimbing', 'type' => 'text', 'required' => FALSE, 'max' => 150, 'col' => 3),
			'kontak_pembimbing' => array('label' => 'Kontak Pembimbing', 'type' => 'text', 'required' => FALSE, 'max' => 50, 'col' => 3),
		),
	),

	// 10 ------------------------------------------------------------------
	'kerja_sama' => array(
		'guide'           => array(
			'purpose' => 'Mengajukan kerja sama resmi antara sekolah dan dunia usaha/dunia industri (DU-DI).',
			'when'    => array('Menjajaki tempat PKL baru', 'Program guru tamu, kelas industri, atau rekrutmen lulusan', 'Sinkronisasi kurikulum dengan industri'),
			'tip'     => 'Isi Bentuk/Bidang Kerja Sama satu butir per baris.',
		),
		'label'           => 'Surat Permohonan Kerja Sama Industri',
		'layout'          => 'dinas',
		'default_subject' => 'Permohonan Kerja Sama',
		'recipient'       => 'required',
		'default_recipient' => 'Pimpinan',
		'attachment'      => TRUE,
		'fields'          => array(
			'nama_instansi'      => array('label' => 'Nama Instansi/Perusahaan', 'type' => 'text', 'required' => TRUE, 'max' => 200, 'col' => 12),
			'bentuk_kerja_sama'  => array('label' => 'Bentuk/Bidang Kerja Sama', 'type' => 'textarea', 'required' => TRUE, 'max' => 2000, 'col' => 12, 'help' => 'Satu butir per baris, misalnya: penempatan siswa PKL, guru tamu, sinkronisasi kurikulum.'),
			'keterangan_tambahan'=> array('label' => 'Keterangan Tambahan', 'type' => 'textarea', 'required' => FALSE, 'max' => 2000, 'col' => 12),
			'narahubung'         => array('label' => 'Narahubung Sekolah', 'type' => 'text', 'required' => FALSE, 'max' => 150, 'col' => 6),
			'telepon_narahubung' => array('label' => 'Telepon Narahubung', 'type' => 'text', 'required' => FALSE, 'max' => 50, 'col' => 6),
		),
	),

	// 11 ------------------------------------------------------------------
	'selesai_pkl' => array(
		'guide'           => array(
			'purpose' => 'Menerangkan bahwa siswa telah menyelesaikan PKL di instansi tertentu pada periode tertentu.',
			'when'    => array('Bukti PKL untuk persyaratan ujian atau kelulusan', 'Lampiran lamaran kerja atau portofolio siswa'),
			'tip'     => 'Predikat/nilai bersifat opsional, isi bila sudah ada dari pembimbing.',
		),
		'label'           => 'Surat Keterangan Telah Melaksanakan PKL',
		'layout'          => 'keterangan',
		'title'           => 'SURAT KETERANGAN',
		'default_subject' => 'Keterangan Telah Melaksanakan PKL',
		'recipient'       => 'hidden',
		'recipient_field' => 'nama_siswa',
		'attachment'      => FALSE,
		'fields'          => array(
			'nama_siswa'          => array('label' => 'Nama Siswa', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
			'nis'                 => array('label' => 'NIS', 'type' => 'text', 'required' => TRUE, 'max' => 30, 'col' => 3),
			'kelas'               => array('label' => 'Kelas', 'type' => 'text', 'required' => FALSE, 'max' => 30, 'col' => 3),
			'kompetensi_keahlian' => array('label' => 'Konsentrasi/Kompetensi Keahlian', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
			'nama_instansi'       => array('label' => 'Nama Instansi/DU-DI', 'type' => 'text', 'required' => TRUE, 'max' => 200, 'col' => 6),
			'alamat_instansi'     => array('label' => 'Alamat Instansi', 'type' => 'text', 'required' => FALSE, 'max' => 255, 'col' => 12),
			'tanggal_mulai'       => array('label' => 'Tanggal Mulai', 'type' => 'date', 'required' => TRUE, 'col' => 4),
			'tanggal_selesai'     => array('label' => 'Tanggal Selesai', 'type' => 'date', 'required' => TRUE, 'col' => 4),
			'predikat'            => array('label' => 'Nilai/Predikat (opsional)', 'type' => 'text', 'required' => FALSE, 'max' => 50, 'col' => 4),
		),
	),

	// 12 ------------------------------------------------------------------
	'rekomendasi' => array(
		'guide'           => array(
			'purpose' => 'Memberikan rekomendasi resmi sekolah atas seorang siswa untuk keperluan tertentu.',
			'when'    => array('Seleksi beasiswa atau pendaftaran perguruan tinggi', 'Seleksi lomba, pertukaran pelajar, atau magang', 'Lamaran kerja lulusan'),
			'tip'     => 'Isi Kepada Yth. bila rekomendasi ditujukan ke lembaga tertentu; prestasi ditulis satu per baris.',
		),
		'label'           => 'Surat Rekomendasi Siswa',
		'layout'          => 'keterangan',
		'title'           => 'SURAT REKOMENDASI',
		'default_subject' => 'Rekomendasi Siswa',
		'recipient'       => 'optional',
		'recipient_field' => 'nama_siswa',
		'attachment'      => FALSE,
		'fields'          => $_siswa + array(
			'kelas'               => array('label' => 'Kelas', 'type' => 'text', 'required' => TRUE, 'max' => 30, 'col' => 6),
			'kompetensi_keahlian' => array('label' => 'Konsentrasi/Kompetensi Keahlian', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
			'tujuan_rekomendasi'  => array('label' => 'Direkomendasikan Untuk', 'type' => 'text', 'required' => TRUE, 'max' => 255, 'col' => 12, 'placeholder' => 'Contoh: mengikuti seleksi beasiswa ...'),
			'prestasi'            => array('label' => 'Prestasi/Catatan Pendukung', 'type' => 'textarea', 'required' => FALSE, 'max' => 2000, 'col' => 12, 'help' => 'Satu butir per baris.'),
		),
	),

	// 13 ------------------------------------------------------------------
	'izin' => array(
		'guide'           => array(
			'purpose' => 'Memberikan izin resmi kepada siswa atau guru/pegawai untuk keperluan tertentu pada waktu tertentu.',
			'when'    => array('Izin siswa mengikuti kegiatan di luar sekolah', 'Izin guru/pegawai meninggalkan tugas untuk keperluan tertentu'),
			'tip'     => 'Kosongkan tanggal selesai bila izin hanya satu hari.',
		),
		'label'           => 'Surat Izin',
		'layout'          => 'keterangan',
		'title'           => 'SURAT IZIN',
		'default_subject' => 'Surat Izin',
		'recipient'       => 'hidden',
		'recipient_field' => 'nama',
		'attachment'      => FALSE,
		'fields'          => array(
			'nama'            => array('label' => 'Nama', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 6),
			'nomor_induk'     => array('label' => 'NIS/NIP', 'type' => 'text', 'required' => FALSE, 'max' => 30, 'col' => 3),
			'jabatan_kelas'   => array('label' => 'Kelas/Jabatan', 'type' => 'text', 'required' => TRUE, 'max' => 100, 'col' => 3),
			'keperluan_izin'  => array('label' => 'Keperluan Izin', 'type' => 'textarea', 'required' => TRUE, 'max' => 1000, 'col' => 12),
			'tanggal_mulai'   => array('label' => 'Tanggal Mulai', 'type' => 'date', 'format' => 'day_date', 'required' => TRUE, 'col' => 4),
			'tanggal_selesai' => array('label' => 'Tanggal Selesai', 'type' => 'date', 'format' => 'day_date', 'required' => FALSE, 'col' => 4),
			'tempat_tujuan'   => array('label' => 'Tempat/Tujuan', 'type' => 'text', 'required' => FALSE, 'max' => 200, 'col' => 4),
		),
	),

	// 14 ------------------------------------------------------------------
	'pengantar' => array(
		'guide'           => array(
			'purpose' => 'Mengantar pengiriman dokumen atau barang, dilengkapi daftar yang dikirim.',
			'when'    => array('Pengiriman laporan atau berkas ke dinas', 'Penyerahan dokumen, sertifikat, atau barang ke instansi lain'),
			'tip'     => 'Isi tabel daftar kiriman: uraian, jumlah, dan keterangan per baris.',
		),
		'label'           => 'Surat Pengantar',
		'layout'          => 'dinas',
		'default_subject' => 'Surat Pengantar',
		'recipient'       => 'required',
		'attachment'      => TRUE,
		'fields'          => array(
			'daftar'         => array('label' => 'Daftar yang Dikirimkan', 'type' => 'rows', 'required' => TRUE, 'col' => 12,
				'columns' => array('uraian' => 'Uraian', 'jumlah' => 'Jumlah', 'keterangan' => 'Keterangan')),
			'isi_pengantar'  => array('label' => 'Keterangan Tambahan (opsional)', 'type' => 'textarea', 'required' => FALSE, 'max' => 2000, 'col' => 12),
		),
	),

	// 15 ------------------------------------------------------------------
	'balasan' => array(
		'guide'           => array(
			'purpose' => 'Membalas surat masuk dari instansi atau pihak lain dengan merujuk nomor, tanggal, dan perihal surat tersebut.',
			'when'    => array('Menjawab undangan atau permohonan dari pihak luar', 'Konfirmasi kesediaan atau penolakan secara resmi'),
			'tip'     => 'Salin nomor surat yang dibalas persis seperti tertulis di surat masuk.',
		),
		'label'           => 'Surat Balasan',
		'layout'          => 'dinas',
		'default_subject' => 'Balasan Surat',
		'recipient'       => 'required',
		'attachment'      => TRUE,
		'fields'          => array(
			'nomor_surat_rujukan'   => array('label' => 'Nomor Surat yang Dibalas', 'type' => 'text', 'required' => TRUE, 'max' => 100, 'col' => 4),
			'tanggal_surat_rujukan' => array('label' => 'Tanggal Surat yang Dibalas', 'type' => 'date', 'required' => TRUE, 'col' => 4),
			'perihal_rujukan'       => array('label' => 'Perihal Surat yang Dibalas', 'type' => 'text', 'required' => TRUE, 'max' => 255, 'col' => 4),
			'isi_balasan'           => array('label' => 'Isi Balasan', 'type' => 'textarea', 'required' => TRUE, 'max' => 4000, 'col' => 12, 'rows' => 6),
		),
	),

	// 16 ------------------------------------------------------------------
	'berita_acara' => array(
		'guide'           => array(
			'purpose' => 'Mencatat secara resmi jalannya suatu kegiatan atau kejadian beserta pihak yang hadir.',
			'when'    => array('Serah terima barang, jabatan, atau dokumen', 'Pelaksanaan ujian, rapat, atau pemusnahan arsip', 'Pencatatan kejadian khusus di sekolah'),
			'tip'     => 'Isi Perihal dengan judul kegiatan agar tercetak di bawah judul BERITA ACARA.',
		),
		'label'           => 'Berita Acara',
		'layout'          => 'keterangan',
		'title'           => 'BERITA ACARA',
		'default_subject' => 'Berita Acara',
		'recipient'       => 'hidden',
		'recipient_field' => 'nama_kegiatan',
		'attachment'      => FALSE,
		'fields'          => array(
			'nama_kegiatan' => array('label' => 'Nama Kegiatan', 'type' => 'text', 'required' => TRUE, 'max' => 255, 'col' => 12),
			'hari_tanggal'  => array('label' => 'Hari/Tanggal', 'type' => 'date', 'format' => 'day_date', 'required' => TRUE, 'col' => 4),
			'waktu'         => array('label' => 'Waktu', 'type' => 'text', 'required' => TRUE, 'max' => 60, 'col' => 4, 'placeholder' => 'Contoh: 08.00 WIB'),
			'tempat'        => array('label' => 'Tempat', 'type' => 'text', 'required' => TRUE, 'max' => 150, 'col' => 4),
			'uraian'        => array('label' => 'Uraian Kegiatan/Kejadian', 'type' => 'textarea', 'required' => TRUE, 'max' => 5000, 'col' => 12, 'rows' => 6),
			'peserta'       => array('label' => 'Pihak yang Hadir (opsional)', 'type' => 'rows', 'required' => FALSE, 'col' => 12,
				'columns' => array('nama' => 'Nama', 'jabatan' => 'Jabatan/Keterangan')),
		),
	),
);

unset($_jk, $_siswa, $_ttl);
