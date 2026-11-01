<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Berita Acara */ ?>
<?php if ($letter['subject'] !== '' && strtoupper($letter['subject']) !== 'BERITA ACARA'): ?>
	<p style="text-align:center; margin-top:-8pt; font-weight:bold"><?= e($letter['subject']) ?></p>
<?php endif; ?>
<?= letter_paragraph($t['opening']) ?>
<p class="justify"><?= nl2br(e($v['uraian']), FALSE) ?></p>
<?php if ( ! empty($v['peserta'])): ?>
	<p>Pihak yang hadir:</p>
	<?= letter_grid($tpl['fields']['peserta']['columns'], $v['peserta']) ?>
<?php endif; ?>
<?= letter_paragraph($t['body']) ?>
<?= letter_paragraph($t['closing']) ?>
