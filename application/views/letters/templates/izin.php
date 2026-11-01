<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Izin */
$waktu = $v['tanggal_mulai'];
if ($v['tanggal_selesai'] !== '' && $v['tanggal_selesai'] !== $v['tanggal_mulai']) { $waktu .= ' s.d. '.$v['tanggal_selesai']; } ?>
<?= letter_paragraph($t['opening']) ?>
<?= letter_data_table(array(
	'Nama'           => $v['nama'],
	'NIS/NIP'        => $v['nomor_induk'],
	'Kelas/Jabatan'  => $v['jabatan_kelas'],
)) ?>
<p class="justify">untuk <?= nl2br(e($v['keperluan_izin']), FALSE) ?>, yang dilaksanakan pada:</p>
<?= letter_data_table(array(
	'Hari/Tanggal'   => $waktu,
	'Tempat/Tujuan'  => $v['tempat_tujuan'],
)) ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_paragraph($t['closing']) ?>
