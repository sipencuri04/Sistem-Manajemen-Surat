<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Permohonan */ ?>
<?= letter_paragraph($t['opening'], '') ?>
<p class="justify"><?= nl2br(e($v['isi_permohonan']), FALSE) ?></p>
<?= letter_list($v['rincian']) ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_paragraph($t['closing']) ?>
