<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Antrean <?= htmlspecialchars($ticket['ticket_number']) ?></title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            width: 48mm;
            margin: 0 auto;
            padding: 8px 4px;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #fff;
            text-align: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .logo {
            width: 44px;
            height: auto;
            margin: 0 auto 5px auto;
            display: block;
        }
        .header h1 {
            font-size: 9.5pt;
            font-weight: 900;
            line-height: 1.2;
            text-transform: uppercase;
            /* TIDAK pakai letter-spacing - menyebabkan pecah di thermal */
            letter-spacing: 0;
        }
        .header h2 {
            font-size: 9.5pt;
            font-weight: 900;
            margin-bottom: 3px;
            letter-spacing: 0;
        }
        .header p {
            font-size: 7pt;
            font-weight: 700;
            margin-bottom: 4px;
        }

        /* Garis tebal SOLID - jangan pakai dashed/dotted di thermal */
        .divider-thick {
            border: none;
            border-top: 2.5pt solid #000;
            margin: 5px 0;
            display: block;
        }
        /* Garis tipis SOLID - bukan dashed */
        .divider-thin {
            border: none;
            border-top: 1pt solid #000;
            margin: 5px 0;
            display: block;
        }

        .queue-label {
            font-size: 7pt;
            font-weight: 900;
            /* TIDAK pakai letter-spacing */
            letter-spacing: 0;
            margin-top: 4px;
            text-transform: uppercase;
        }
        /* Nomor antrean: ukuran diturunkan, letter-spacing NOLL */
        .queue-number {
            font-size: 30pt;
            font-weight: 900;
            letter-spacing: 0;      /* <-- FIX UTAMA: hilangkan celah antar karakter */
            line-height: 1.05;
            margin: 4px 0;
        }
        .service-name {
            font-size: 8pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0;
            line-height: 1.2;
        }
        .counter-name {
            font-size: 9pt;
            font-weight: 900;
            margin-top: 2px;
            letter-spacing: 0;
        }

        .meta-table {
            width: 100%;
            margin: 4px 0;
            font-size: 7pt;
            font-weight: 700;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .meta-table .label { text-align: left; }
        .meta-table .val   { text-align: right; }

        .footer {
            font-size: 6pt;
            font-weight: 700;
            line-height: 1.35;
            margin-top: 4px;
        }

        @media print {
            .no-print { display: none !important; }
        }
        .no-print {
            margin-top: 18px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .no-print button {
            padding: 7px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
        }
        .btn-print { background: #1d4ed8; color: #fff; border: none; }
        .btn-close  { background: #f1f5f9; color: #333; border: 1px solid #ccc; }
    </style>
</head>
<body>

    <div class="header">
        <img src="<?= base_url('assets/img/logo-sekolah.png') ?>" alt="Logo SMK" class="logo">
        <h1><?= htmlspecialchars($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?></h1>
        <p>Layanan Tata Usaha &amp; Administrasi</p>
    </div>

    <hr class="divider-thick">

    <div class="queue-section">
        <div class="queue-label">NOMOR ANTREAN ANDA</div>
        <div class="queue-number"><?= htmlspecialchars($ticket['ticket_number']) ?></div>
        <div class="service-name"><?= htmlspecialchars($ticket['service_name']) ?></div>
        <div class="counter-name">LOKET <?= (int) $ticket['loket_number'] ?></div>
    </div>

    <hr class="divider-thick">

    <table class="meta-table">
        <tr>
            <td class="label">Tanggal</td>
            <td class="val"><?= date('d/m/Y') ?></td>
        </tr>
        <tr>
            <td class="label">Waktu Ambil</td>
            <td class="val"><?= date('H:i') ?> WIB</td>
        </tr>
        <tr>
            <td class="label">Antrian di Depan</td>
            <td class="val"><?= (int) $ticket['waiting_before'] ?> Orang</td>
        </tr>
    </table>

    <hr class="divider-thin">

    <div class="footer">
        Simpan tiket &amp; perhatikan layar monitor.<br>
        Terima kasih telah tertib antre.
    </div>

    <!-- Tombol hanya muncul di layar, tidak ikut tercetak -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">&#x1F5A8; Cetak</button>
        <button class="btn-close"  onclick="window.close()">Tutup</button>
    </div>

    <script>
        // Auto-print otomatis saat halaman dibuka (delay 400ms agar render selesai)
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 400);
        });
    </script>

</body>
</html>
