<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Tugas Guru/Pegawai */
$dasar = text_lines($v['dasar']);
$waktu = $v['tanggal_mulai'];
if ($v['tanggal_selesai'] !== '' && $v['tanggal_selesai'] !== $v['tanggal_mulai']) { $waktu .= ' s.d. '.$v['tanggal_selesai']; } ?>
<?php if ( ! empty($dasar)): ?>
<table class="data" style="margin-left:0">
	<tr><td class="lbl" style="width:2.2cm">Dasar</td><td class="sep">:</td><td>
		<?php foreach ($dasar as $i => $line): ?><?= count($dasar) > 1 ? ($i + 1).'. ' : '' ?><?= e($line) ?><br><?php endforeach; ?>
	</td></tr>
</table>
<?php endif; ?>
<?= letter_paragraph($t['opening']) ?>
<?= letter_grid($tpl['fields']['petugas']['columns'], $v['petugas']) ?>
<table class="data" style="margin-left:0">
	<tr><td class="lbl" style="width:2.2cm">Untuk</td><td class="sep">:</td><td class="justify"><?= nl2br(e($v['uraian_tugas']), FALSE) ?></td></tr>
	<tr><td class="lbl">Waktu</td><td class="sep">:</td><td><?= e($waktu) ?></td></tr>
	<tr><td class="lbl">Tempat</td><td class="sep">:</td><td><?= e($v['tempat']) ?></td></tr>
</table>
<?= letter_paragraph($t['body']) ?>
<?= letter_paragraph($t['closing']) ?>
