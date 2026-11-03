<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<title>Tiket Antrean <?= e($ticket['ticket_number']) ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		/* ===================================================
		 * TIKET ANTREAN - Dioptimasi untuk Printer Thermal
		 * 58mm (WOYA, POS-58, RPP02, XP-58)
		 * Area cetak efektif: 48mm = ~162px @ 96dpi
		 * TIDAK menggunakan letter-spacing, border-radius,
		 * background-color, atau property yang menyebabkan
		 * anti-aliasing di driver printer thermal.
		 * =================================================== */
		@page {
			margin: 0;
			size: 58mm auto;
		}
		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}
		body {
			font-family: Arial, Helvetica, sans-serif;
			margin: 0;
			padding: 6px 5px 10px 5px;
			color: #000;
			background: #fff;
			width: 48mm;
			text-align: center;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
			font-size: 8pt;
			line-height: 1.3;
		}

		/* --- Header Sekolah --- */
		.hdr-nama {
			font-size: 8.5pt;
			font-weight: 900;
			text-transform: uppercase;
			line-height: 1.25;
			margin-bottom: 1px;
		}
		.hdr-sub {
			font-size: 6.5pt;
			font-weight: 700;
			margin-bottom: 4px;
		}

		/* --- Garis pemisah: pakai HR bukan border-top (lebih solid di thermal) --- */
		hr {
			border: none;
			border-top: 1.5pt solid #000;
			margin: 4px 0;
			display: block;
			height: 0;
		}
		hr.thin {
			border-top-width: 0.75pt;
			margin: 3px 0;
		}

		/* --- Nomor antrean besar --- */
		.lbl-no {
			font-size: 6.5pt;
			font-weight: 900;
			text-transform: uppercase;
			margin-top: 2px;
			margin-bottom: 1px;
			letter-spacing: 0;
		}
		.no-antrean {
			font-size: 28pt;
			font-weight: 900;
			line-height: 1.05;
			letter-spacing: 0;
			margin: 2px 0;
		}
		.lbl-layanan {
			font-size: 7.5pt;
			font-weight: 900;
			text-transform: uppercase;
			line-height: 1.2;
			margin-bottom: 1px;
		}
		.lbl-loket {
			font-size: 8pt;
			font-weight: 900;
			margin-bottom: 3px;
			letter-spacing: 0;
		}

		/* --- Tabel info --- */
		.info-tbl {
			width: 100%;
			border-collapse: collapse;
			margin: 3px 0;
			text-align: left;
		}
		.info-tbl td {
			font-size: 6.5pt;
			font-weight: 700;
			padding: 1px 0;
		}
		.info-tbl td.val {
			text-align: right;
		}

		/* --- Footer --- */
		.footer {
			font-size: 5.5pt;
			font-weight: 700;
			margin-top: 4px;
			line-height: 1.35;
		}

		/* --- Tombol hanya di layar --- */
		@media print {
			.no-print { display: none !important; }
		}
		.no-print {
			margin-top: 16px;
			display: flex;
			gap: 8px;
			justify-content: center;
		}
		.no-print button {
			padding: 8px 18px;
			font-size: 13px;
			font-weight: bold;
			cursor: pointer;
			border-radius: 6px;
			border: 1px solid #ccc;
		}
		.btn-print { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
		.btn-close  { background: #f1f5f9; color: #333; }
	</style>
</head>
<body>

	<div class="hdr-nama"><?= htmlspecialchars($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?></div>
	<div class="hdr-sub">Layanan Tata Usaha &amp; Administrasi</div>

	<hr>

	<div class="lbl-no">NOMOR ANTREAN ANDA</div>
	<div class="no-antrean"><?= htmlspecialchars($ticket['ticket_number']) ?></div>
	<div class="lbl-layanan"><?= htmlspecialchars($ticket['service_name']) ?></div>
	<div class="lbl-loket">LOKET <?= (int) $ticket['loket_number'] ?></div>

	<hr>

	<table class="info-tbl">
		<tr>
			<td>Tanggal</td>
			<td class="val"><?= date('d/m/Y') ?></td>
		</tr>
		<tr>
			<td>Waktu Ambil</td>
			<td class="val"><?= date('H:i') ?> WIB</td>
		</tr>
		<tr>
			<td>Antrian di Depan</td>
			<td class="val"><?= (int) $ticket['waiting_before'] ?> Orang</td>
		</tr>
	</table>

	<hr class="thin">

	<div class="footer">
		Simpan tiket &amp; perhatikan layar monitor.<br>
		Terima kasih telah tertib antre.
	</div>

	<div class="no-print">
		<button class="btn-print" onclick="window.print()">&#x1F5A8; Cetak Tiket</button>
		<button class="btn-close"  onclick="window.close()">Tutup</button>
	</div>

	<script>
		// Selalu auto-print otomatis saat halaman tiket dibuka
		// Delay 400ms memastikan font & layout sudah selesai di-render
		// sebelum dialog cetak muncul (mencegah cetakan kosong/terpotong)
		window.addEventListener('load', function() {
			setTimeout(function() {
				window.print();
			}, 400);
		});
	</script>

</body>
</html>

