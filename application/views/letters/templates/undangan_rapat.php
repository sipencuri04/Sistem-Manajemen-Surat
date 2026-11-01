<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Undangan Rapat */
$agenda = text_lines($v['acara']); ?>
<?= letter_paragraph($t['opening']) ?>
<table class="data">
	<tr><td class="lbl">Hari/Tanggal</td><td class="sep">:</td><td><?= e($v['hari_tanggal']) ?></td></tr>
	<tr><td class="lbl">Waktu</td><td class="sep">:</td><td><?= e($v['waktu']) ?></td></tr>
	<tr><td class="lbl">Tempat</td><td class="sep">:</td><td><?= e($v['tempat']) ?></td></tr>
	<tr><td class="lbl">Acara</td><td class="sep">:</td><td>
		<?php if (count($agenda) > 1): ?>
			<?php foreach ($agenda as $i => $a): ?><?= ($i + 1).'. '.e($a) ?><br><?php endforeach; ?>
		<?php else: ?><?= e($v['acara']) ?><?php endif; ?>
	</td></tr>
</table>
<?= letter_paragraph($t['body']) ?>
<?php if ($v['catatan'] !== ''): ?><p class="justify"><em>Catatan:</em> <?= nl2br(e($v['catatan']), FALSE) ?></p><?php endif; ?>
<?= letter_paragraph($t['closing']) ?>
