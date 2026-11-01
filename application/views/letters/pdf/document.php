<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Dokumen surat resmi (A4). Dipakai untuk PDF (Dompdf) dan pratinjau HTML.
 *
 * @var string $mode       'pdf' | 'html'
 * @var array  $letter     data surat
 * @var array  $tpl        konfigurasi template
 * @var array  $school     identitas sekolah
 * @var array  $images     data URI logo/ttd/stempel
 * @var string $body_html  isi surat dari letters/templates/<kunci>.php
 * @var string|null $watermark
 */
$kop_mode   = $school['kop_template'];
$show_left  = $kop_mode !== 'tanpa_logo' && $images['logo'];
$show_right = $kop_mode === 'logo_kiri_kanan' && $images['logo_right'];
$contacts = array();
if ($school['phone'] !== '')   $contacts[] = 'Telp. '.$school['phone'];
if ($school['email'] !== '')   $contacts[] = 'Email: '.$school['email'];
if ($school['website'] !== '') $contacts[] = 'Website: '.$school['website'];
$address = $school['address'];
if ($school['postal_code'] !== '') $address .= ($address !== '' ? ' ' : '').'Kode Pos '.$school['postal_code'];
$place_date = e($letter['place']).', '.e(tgl_indo($letter['letter_date']));
$show_nip = (int) $school['show_nip'] && trim($letter['signer_nip']) !== '';
$is_pdf = $mode === 'pdf';

ob_start(); ?>
<style>
<?php if ($is_pdf): ?>
	@page { size: A4 portrait; margin: 1.5cm 2cm 2cm 2.5cm; }
	body { margin: 0; }
<?php endif; ?>
	.letter-doc { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.45; color: #000; position: relative; }
	.letter-doc table { border-collapse: collapse; }
	.letter-doc p { margin: 0 0 8pt 0; }
	.letter-doc .kop { width: 100%; }
	.letter-doc .kop td { vertical-align: middle; text-align: center; padding: 0; }
	.letter-doc .kop .logo { width: 2.4cm; }
	.letter-doc .kop .logo img { max-width: 2.3cm; max-height: 2.4cm; }
	.letter-doc .kop .l1 { font-size: 13pt; text-transform: uppercase; line-height: 1.2; }
	.letter-doc .kop .name { font-size: 17pt; font-weight: bold; text-transform: uppercase; line-height: 1.2; }
	.letter-doc .kop .addr { font-size: 10pt; line-height: 1.25; }
	.letter-doc .kop-line { border-top: 3px solid #000; border-bottom: 1px solid #000; height: 2px; margin: 6pt 0 14pt 0; }
	.letter-doc .kop-empty { border: 1px dashed #c00; color: #c00; font-family: Arial, Helvetica, sans-serif; font-size: 10pt; padding: 8pt; text-align: center; margin-bottom: 12pt; }
	.letter-doc .meta td { vertical-align: top; padding: 0 0 1pt 0; }
	.letter-doc .title { text-align: center; margin: 4pt 0 14pt 0; }
	.letter-doc .title .t { font-size: 13pt; font-weight: bold; text-decoration: underline; letter-spacing: .5pt; }
	.letter-doc .title .n { font-size: 12pt; }
	.letter-doc .recipient { margin: 12pt 0 14pt 0; }
	.letter-doc .justify { text-align: justify; }
	.letter-doc .indent { padding-left: 1cm; }
	.letter-doc .data { margin: 2pt 0 10pt 1cm; }
	.letter-doc .data td { vertical-align: top; padding: 1pt 4pt 1pt 0; }
	.letter-doc .data td.lbl { width: 5.2cm; }
	.letter-doc .data td.sep { width: .4cm; }
	.letter-doc .grid { width: 100%; margin: 4pt 0 10pt 0; }
	.letter-doc .grid th, .letter-doc .grid td { border: 1px solid #000; padding: 3pt 5pt; vertical-align: top; font-size: 11pt; }
	.letter-doc .grid th { background: #eee; text-align: center; font-weight: bold; }
	.letter-doc .grid td.no { text-align: center; width: 1cm; }
	.letter-doc ol.list { margin: 0 0 8pt 0; padding-left: 1.6cm; }
	.letter-doc ol.list li { margin-bottom: 1pt; }
	.letter-doc .sign { width: 100%; margin-top: 18pt; page-break-inside: avoid; }
	.letter-doc .sign td { vertical-align: top; padding: 0; }
	.letter-doc .sign .space { height: 2.2cm; position: relative; }
	.letter-doc .sign .space img.ttd { height: 1.8cm; max-width: 6cm; margin: .25cm 0 0 .3cm; }
	.letter-doc .sign .space img.stamp { height: 2.6cm; position: absolute; left: -1.4cm; top: -.3cm; opacity: .85; }
	.letter-doc .sign .nm { font-weight: bold; text-decoration: underline; }
	.letter-doc .watermark { position: <?= $is_pdf ? 'fixed' : 'absolute' ?>; top: 38%; left: 8%; width: 84%; text-align: center; font-size: 80pt; font-weight: bold; color: #000; opacity: .07; transform: rotate(-30deg); z-index: 0; font-family: Arial, Helvetica, sans-serif; }
<?php if ( ! $is_pdf): ?>
	.letter-sheet { background: #fff; width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 1.5cm 2cm 2cm 2.5cm; box-sizing: border-box; box-shadow: 0 4px 24px rgba(10,30,60,.15); position: relative; overflow: hidden; }
	@media print { .letter-sheet { box-shadow: none; width: auto; min-height: 0; padding: 0; } }
<?php endif; ?>
</style>
<?php $css = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="letter-doc">
	<?php if ($watermark): ?><div class="watermark"><?= e($watermark) ?></div><?php endif; ?>

	<?php /* ---------------- KOP SURAT ---------------- */ ?>
	<?php if (trim($school['school_name']) === ''): ?>
		<div class="kop-empty">Kop surat belum diatur. Isi identitas sekolah pada menu Pengaturan Sekolah.</div>
	<?php else: ?>
		<table class="kop">
			<tr>
				<?php if ($show_left OR $show_right): ?>
					<td class="logo"><?php if ($show_left): ?><img src="<?= $images['logo'] ?>" alt="Logo"><?php endif; ?></td>
				<?php endif; ?>
				<td>
					<?php if ($school['kop_line1'] !== ''): ?><div class="l1"><?= e($school['kop_line1']) ?></div><?php endif; ?>
					<?php if ($school['kop_line2'] !== ''): ?><div class="l1"><?= e($school['kop_line2']) ?></div><?php endif; ?>
					<div class="name"><?= e($school['school_name']) ?></div>
					<?php if ($school['npsn'] !== ''): ?><div class="addr">NPSN: <?= e($school['npsn']) ?></div><?php endif; ?>
					<?php if ($address !== ''): ?><div class="addr"><?= e($address) ?></div><?php endif; ?>
					<?php if ( ! empty($contacts)): ?><div class="addr"><?= e(implode(' | ', $contacts)) ?></div><?php endif; ?>
				</td>
				<?php if ($show_left OR $show_right): ?>
					<td class="logo"><?php if ($show_right): ?><img src="<?= $images['logo_right'] ?>" alt="Logo"><?php endif; ?></td>
				<?php endif; ?>
			</tr>
		</table>
		<div class="kop-line"></div>
	<?php endif; ?>

	<?php /* ---------------- KEPALA SURAT ---------------- */ ?>
	<?php if ($tpl['layout'] === 'keterangan'): ?>
		<div class="title">
			<div class="t"><?= e($tpl['title']) ?></div>
			<div class="n">Nomor: <?= e($letter['letter_number']) ?></div>
		</div>
		<?php if ($tpl['recipient'] !== 'hidden' && ! empty($letter['recipient']) && $letter['recipient'] !== ($tpl['recipient_field'] && isset($d[$tpl['recipient_field']]) ? $d[$tpl['recipient_field']] : NULL)): ?>
			<div class="recipient">Kepada Yth.<br><strong><?= e($letter['recipient']) ?></strong><br>di <?= e($letter['recipient_address'] ?: 'Tempat') ?></div>
		<?php endif; ?>
	<?php else: ?>
		<table class="meta" style="width:100%">
			<tr>
				<td style="width:2.3cm">Nomor</td><td style="width:.4cm">:</td><td><?= e($letter['letter_number']) ?></td>
				<td style="text-align:right; white-space:nowrap"><?= $place_date ?></td>
			</tr>
			<?php if ($tpl['attachment']): ?>
			<tr><td>Lampiran</td><td>:</td><td colspan="2"><?= e($letter['attachment_text'] !== NULL && $letter['attachment_text'] !== '' ? $letter['attachment_text'] : '-') ?></td></tr>
			<?php endif; ?>
			<tr><td>Perihal</td><td>:</td><td colspan="2"><strong><?= e($letter['subject']) ?></strong></td></tr>
		</table>
		<div class="recipient">
			Kepada Yth.<br>
			<strong><?= e($letter['recipient'] ?: '..............................') ?></strong><br>
			di <?= e($letter['recipient_address'] ?: 'Tempat') ?>
		</div>
	<?php endif; ?>

	<?php /* ---------------- ISI SURAT ---------------- */ ?>
	<?= $body_html ?>

	<?php /* ---------------- PENUTUP & TANDA TANGAN ---------------- */ ?>
	<table class="sign">
		<tr>
			<td style="width:55%"></td>
			<td>
				<?php if ($tpl['layout'] === 'keterangan'): ?><div><?= $place_date ?></div><?php endif; ?>
				<div><?= e($letter['signer_title']) ?>,</div>
				<div class="space">
					<?php if ($images['signature']): ?><img class="ttd" src="<?= $images['signature'] ?>" alt="Tanda tangan"><?php endif; ?>
					<?php if ($images['stamp']): ?><img class="stamp" src="<?= $images['stamp'] ?>" alt="Stempel"><?php endif; ?>
				</div>
				<div class="nm"><?= e($letter['signer_name'] !== '' ? $letter['signer_name'] : '....................................') ?></div>
				<?php if ($show_nip): ?><div>NIP. <?= e($letter['signer_nip']) ?></div><?php endif; ?>
			</td>
		</tr>
	</table>
</div>
<?php $doc = ob_get_clean(); ?>
<?php if ($is_pdf): ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= e($letter['letter_number']) ?></title>
<?= $css ?>
</head>
<body><?= $doc ?></body>
</html>
<?php else: ?>
<?= $css ?>
<div class="letter-sheet"><?= $doc ?></div>
<?php endif; ?>
