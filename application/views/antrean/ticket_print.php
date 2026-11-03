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
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            /* Dibuat 42mm agar aman di semua jenis roll thermal 58mm tanpa terpotong kanan-kiri */
            width: 42mm;
            max-width: 42mm;
            margin: 0 auto;
            padding: 2px;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background: #ffffff;
            text-align: center;
        }
        .header h1 {
            font-size: 11px;
            font-weight: 900;
            line-height: 1.15;
            text-transform: uppercase;
            letter-spacing: 0;
            word-wrap: break-word;
        }
        .header p {
            font-size: 8px;
            font-weight: bold;
            margin-top: 2px;
            line-height: 1.15;
        }
        .divider {
            border-top: 1.5px solid #000000;
            margin: 4px 0;
        }
        .queue-label {
            font-size: 8.5px;
            font-weight: 900;
            letter-spacing: 0;
            margin-top: 1px;
        }
        .queue-number {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 0;
            line-height: 1.05;
            margin: 2px 0;
            font-family: Arial, sans-serif;
        }
        .service-name {
            font-size: 9.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0;
            line-height: 1.15;
            word-wrap: break-word;
        }
        .counter-name {
            font-size: 11px;
            font-weight: 900;
            margin-top: 1px;
            letter-spacing: 0;
        }
        .meta-table {
            width: 100%;
            margin: 3px 0;
            font-size: 8.5px;
            font-weight: bold;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1px 0;
        }
        .meta-table .label { text-align: left; }
        .meta-table .val   { text-align: right; }
        .footer {
            font-size: 7.5px;
            font-weight: bold;
            line-height: 1.25;
            margin-top: 3px;
        }
        @media print {
            .no-print { display: none !important; }
            body { width: 42mm; max-width: 42mm; margin: 0 auto; padding: 1px; }
        }
        .no-print {
            margin-top: 12px;
            display: flex;
            gap: 6px;
            justify-content: center;
        }
        .no-print button {
            padding: 5px 12px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-print { background: #1d4ed8; color: #fff; border: none; }
        .btn-close  { background: #f1f5f9; color: #333; border: 1px solid #ccc; }
    </style>
</head>
<body>

    <div class="header">
        <h1>SMK MUHAMMADIYAH BANDONGAN</h1>
        <p>Layanan Tata Usaha &amp; Administrasi</p>
    </div>

    <div class="divider"></div>

    <div class="queue-section">
        <div class="queue-label">NOMOR ANTREAN ANDA</div>
        <div class="queue-number"><?= htmlspecialchars($ticket['ticket_number']) ?></div>
        <div class="service-name"><?= htmlspecialchars($ticket['service_name']) ?></div>
        <div class="counter-name">MENUJU LOKET <?= (int) $ticket['loket_number'] ?></div>
    </div>

    <div class="divider"></div>

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
            <td class="label">Antrean di Depan</td>
            <td class="val"><?= (int) $ticket['waiting_before'] ?> Orang</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer">
        Simpan tiket &amp; perhatikan monitor.<br>
        Terima kasih telah tertib antre.
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak</button>
        <button class="btn-close" onclick="window.close()">Tutup</button>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() { 
                window.focus();
                window.print(); 
            }, 250);
        });
    </script>

</body>
</html>
