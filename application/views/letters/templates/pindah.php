<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Keterangan Pindah Sekolah */ ?>
<?= letter_paragraph($t['opening']) ?>
<?= letter_data_table(array(
	'Nama'                  => $v['nama_siswa'],
	'Tempat, Tanggal Lahir' => trim($v['tempat_lahir'].', '.$v['tanggal_lahir'], ', '),
	'Jenis Kelamin'         => $v['jenis_kelamin'],
	'NIS / NISN'            => trim($v['nis'].($v['nisn'] !== '' ? ' / '.$v['nisn'] : '')),
	'Kelas'                 => $v['kelas'],
	'Konsentrasi Keahlian'  => $v['kompetensi_keahlian'],
	'Nama Orang Tua/Wali'   => $v['nama_orang_tua'],
	'Alamat'                => $v['alamat'],
)) ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_data_table(array(
	'Sekolah Tujuan'        => $v['sekolah_tujuan'],
	'Alamat Sekolah Tujuan' => $v['alamat_sekolah_tujuan'],
)) ?>
<?= letter_paragraph($t['closing']) ?>
