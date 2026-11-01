<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Pengantar PKL */ ?>
<?= letter_paragraph($t['opening'], '') ?>
<?= letter_grid($tpl['fields']['siswa']['columns'], $v['siswa']) ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_data_table(array(
	'Guru Pembimbing'   => $v['guru_pembimbing'],
	'Kontak Pembimbing' => $v['kontak_pembimbing'],
)) ?>
<?= letter_paragraph($t['closing']) ?>
