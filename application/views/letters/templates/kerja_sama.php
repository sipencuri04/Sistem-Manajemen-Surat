<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Permohonan Kerja Sama Industri */ ?>
<?= letter_paragraph($t['opening'], 'justify') ?>
<?= letter_list($v['bentuk_kerja_sama']) ?>
<?php if ($v['keterangan_tambahan'] !== ''): ?><p class="justify"><?= nl2br(e($v['keterangan_tambahan']), FALSE) ?></p><?php endif; ?>
<?= letter_paragraph($t['body']) ?>
<?php if ($v['narahubung'] !== ''): ?>
<p class="justify">Untuk informasi lebih lanjut, Bapak/Ibu dapat menghubungi <?= e($v['narahubung']) ?><?= $v['telepon_narahubung'] !== '' ? ' (telepon '.e($v['telepon_narahubung']).')' : '' ?>.</p>
<?php endif; ?>
<?= letter_paragraph($t['closing']) ?>
