<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Panggilan Orang Tua/Wali */ ?>
<?= letter_paragraph($t['opening']) ?>
<?= letter_data_table(array(
	'Nama Siswa' => $v['nama_siswa'],
	'Kelas'      => $v['kelas'],
	'NIS'        => $v['nis'],
)) ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_data_table(array(
	'Hari/Tanggal' => $v['hari_tanggal'],
	'Waktu'        => $v['waktu'],
	'Tempat'       => $v['tempat'],
	'Keperluan'    => $v['keperluan'],
	'Menemui'      => $v['menemui'],
)) ?>
<?= letter_paragraph($t['closing']) ?>
