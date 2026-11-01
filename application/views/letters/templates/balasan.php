<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Balasan */ ?>
<?= letter_paragraph($t['opening'], 'justify') ?>
<p class="justify"><?= nl2br(e($v['isi_balasan']), FALSE) ?></p>
<?= letter_paragraph($t['body']) ?>
<?= letter_paragraph($t['closing']) ?>
