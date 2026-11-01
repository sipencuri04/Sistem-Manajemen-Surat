<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Pemberitahuan */ ?>
<?= letter_paragraph($t['opening'], '') ?>
<p class="justify"><?= nl2br(e($v['isi_pemberitahuan']), FALSE) ?></p>
<?= letter_data_table(array(
	'Hari/Tanggal' => $v['hari_tanggal'],
	'Waktu'        => $v['waktu'],
	'Tempat'       => $v['tempat'],
)) ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_paragraph($t['closing']) ?>
