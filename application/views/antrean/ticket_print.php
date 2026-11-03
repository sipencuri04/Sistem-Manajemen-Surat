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
            width: 48mm;
            margin: 0 auto;
            padding: 4px 2px;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background: #ffffff;
            text-align: center;
        }
        .header h1 {
            font-size: 13px;
            font-weight: 900;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        .header p {
            font-size: 9px;
            font-weight: bold;
            margin-top: 2px;
            line-height: 1.2;
        }
        .divider {
            border-top: 2px solid #000000;
            margin: 5px 0;
        }
        .queue-label {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0;
            margin-top: 2px;
        }
        .queue-number {
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 0;
            line-height: 1.1;
            margin: 2px 0;
            font-family: Arial, sans-serif;
        }
        .service-name {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0;
            line-height: 1.2;
        }
        .counter-name {
            font-size: 13px;
            font-weight: 900;
            margin-top: 2px;
            letter-spacing: 0;
        }
        .meta-table {
            width: 100%;
            margin: 4px 0;
            font-size: 9.5px;
            font-weight: bold;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1px 0;
        }
        .meta-table .label { text-align: left; }
        .meta-table .val   { text-align: right; }
        .footer {
            font-size: 8.5px;
            font-weight: bold;
            line-height: 1.3;
            margin-top: 4px;
        }
        @media print {
            .no-print { display: none !important; }
            body { width: 48mm; margin: 0 auto; padding: 2px; }
        }
        .no-print {
            margin-top: 15px;
            display: flex;
            gap: 6px;
            justify-content: center;
        }
        .no-print button {
            padding: 6px 14px;
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
        // Jalankan perintah print segera setelah dimuat
        window.addEventListener('load', function() {
            setTimeout(function() { 
                window.focus();
                window.print(); 
            }, 250);
        });
    </script>

</body>
</html>
