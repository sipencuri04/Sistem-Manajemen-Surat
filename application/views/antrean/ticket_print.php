<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<title>Tiket Antrean <?= e($ticket['ticket_number']) ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		@page { margin: 0; size: 80mm auto; }
		body {
			font-family: 'Courier New', Courier, monospace;
			margin: 0;
			padding: 15px;
			color: #000;
			background: #fff;
			width: 72mm;
			max-width: 100%;
			text-align: center;
		}
		.title { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
		.sub { font-size: 11px; margin-bottom: 8px; }
		.line { border-top: 1px dashed #000; margin: 8px 0; }
		.num { font-size: 42px; font-weight: 900; margin: 6px 0; font-family: Arial, sans-serif; }
		.service { font-size: 13px; font-weight: bold; text-transform: uppercase; }
		.loket { font-size: 14px; font-weight: bold; margin-top: 4px; }
		.meta { font-size: 11px; text-align: left; margin: 8px 0; }
		.meta table { width: 100%; border-collapse: collapse; }
		.meta td { padding: 2px 0; font-size: 11px; }
		.footer { font-size: 10px; margin-top: 10px; font-style: italic; }
		@media print {
			.no-print { display: none !important; }
		}
	</style>
</head>
<body>
	<div class="title"><?= htmlspecialchars($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?></div>
	<div class="sub">Layanan Tata Usaha &amp; Administrasi</div>
	<div class="line"></div>
	<div style="font-size: 11px;">NOMOR ANTREAN ANDA</div>
	<div class="num"><?= htmlspecialchars($ticket['ticket_number']) ?></div>
	<div class="service"><?= htmlspecialchars($ticket['service_name']) ?></div>
	<div class="loket">MENUJU LOKET <?= (int) $ticket['loket_number'] ?></div>
	<div class="line"></div>
	<div class="meta">
		<table>
			<tr>
				<td>Tanggal:</td>
				<td style="text-align: right; font-weight: bold;"><?= date('d/m/Y') ?></td>
			</tr>
			<tr>
				<td>Waktu Ambil:</td>
				<td style="text-align: right; font-weight: bold;"><?= date('H:i') ?> WIB</td>
			</tr>
			<tr>
				<td>Antrean di Depan:</td>
				<td style="text-align: right; font-weight: bold;"><?= (int) $ticket['waiting_before'] ?> Orang</td>
			</tr>
		</table>
	</div>
	<div class="line"></div>
	<div class="footer">
		Harap simpan tiket ini dan perhatikan layar monitor TV ruang tunggu.<br>
		Terima kasih telah tertib antre.
	</div>

	<div class="no-print" style="margin-top: 20px;">
		<button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 6px;">Cetak Tiket</button>
		<button onclick="window.close()" style="padding: 8px 16px; cursor: pointer; border-radius: 6px; border: 1px solid #ccc; background: #f8fafc;">Tutup</button>
	</div>

	<script>
		// Hanya auto-print bila parameter autoprint=1 disertakan
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('autoprint') === '1') {
			window.addEventListener('load', function() { window.print(); });
		}
	</script>
</body>
</html>

