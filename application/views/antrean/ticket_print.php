<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<title>Tiket Antrean <?= e($ticket['ticket_number']) ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		@page { 
			margin: 0; 
			size: 58mm auto; 
		}
		* {
			box-sizing: border-box;
		}
		body {
			font-family: Arial, Helvetica, sans-serif;
			margin: 0 auto;
			padding: 4px 6px;
			color: #000000;
			background: #ffffff;
			width: 48mm;
			max-width: 48mm;
			text-align: center;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}
		.title { font-size: 11px; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; line-height: 1.2; word-wrap: break-word; }
		.sub { font-size: 8.5px; margin-bottom: 4px; font-weight: 600; line-height: 1.2; }
		.line-solid { border-top: 2px solid #000000; margin: 5px 0; }
		.line-thin { border-top: 1px solid #000000; margin: 4px 0; }
		.num-caption { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
		.num { font-size: 34px; font-weight: 900; margin: 2px 0; font-family: Arial, sans-serif; letter-spacing: 1px; line-height: 1.1; }
		.service { font-size: 11px; font-weight: 800; text-transform: uppercase; margin-top: 2px; line-height: 1.2; }
		.loket { font-size: 12px; font-weight: 900; margin-top: 3px; letter-spacing: 0.5px; }
		.meta { font-size: 9.5px; text-align: left; margin: 5px 0; font-weight: 700; }
		.meta table { width: 100%; border-collapse: collapse; }
		.meta td { padding: 2px 0; font-size: 9.5px; }
		.footer { font-size: 8px; margin-top: 5px; font-weight: 600; line-height: 1.3; }
		@media print {
			.no-print { display: none !important; }
			body { width: 100%; max-width: 48mm; margin: 0; padding: 2px 4px; }
		}
	</style>
</head>
<body>
	<div class="title"><?= htmlspecialchars($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?></div>
	<div class="sub">Layanan Tata Usaha &amp; Administrasi</div>
	<div class="line-solid"></div>
	<div class="num-caption">NOMOR ANTREAN ANDA</div>
	<div class="num"><?= htmlspecialchars($ticket['ticket_number']) ?></div>
	<div class="service"><?= htmlspecialchars($ticket['service_name']) ?></div>
	<div class="loket">MENUJU LOKET <?= (int) $ticket['loket_number'] ?></div>
	<div class="line-solid"></div>
	<div class="meta">
		<table>
			<tr>
				<td>Tanggal:</td>
				<td style="text-align: right; font-weight: 800;"><?= date('d/m/Y') ?></td>
			</tr>
			<tr>
				<td>Waktu Ambil:</td>
				<td style="text-align: right; font-weight: 800;"><?= date('H:i') ?> WIB</td>
			</tr>
			<tr>
				<td>Antrean di Depan:</td>
				<td style="text-align: right; font-weight: 800;"><?= (int) $ticket['waiting_before'] ?> Orang</td>
			</tr>
		</table>
	</div>
	<div class="line-thin"></div>
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

