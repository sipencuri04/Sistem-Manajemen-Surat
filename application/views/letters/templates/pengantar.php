<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Pengantar */ ?>
<?= letter_paragraph($t['opening'], '') ?>
<?= letter_grid($tpl['fields']['daftar']['columns'], $v['daftar']) ?>
<?php if ($v['isi_pengantar'] !== ''): ?><p class="justify"><?= nl2br(e($v['isi_pengantar']), FALSE) ?></p><?php endif; ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_paragraph($t['closing']) ?>
