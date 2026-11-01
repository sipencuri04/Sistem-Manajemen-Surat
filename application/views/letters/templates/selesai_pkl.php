<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Keterangan Telah Melaksanakan PKL */ ?>
<?= letter_paragraph($t['opening']) ?>
<?= letter_data_table(array(
	'Nama'                 => $v['nama_siswa'],
	'NIS'                  => $v['nis'],
	'Kelas'                => $v['kelas'],
	'Konsentrasi Keahlian' => $v['kompetensi_keahlian'],
)) ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_data_table(array(
	'Tempat PKL'      => $v['nama_instansi'],
	'Alamat'          => $v['alamat_instansi'],
	'Nilai/Predikat'  => $v['predikat'],
)) ?>
<?= letter_paragraph($t['closing']) ?>
