<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Konfigurasi Email / SMTP Persuratan SMK.
 * Menggunakan Environment Variable sehingga kredensial aman dan tidak tertulis di kode.
 *
 * Environment variables:
 * - SMTP_HOST       (contoh: smtp.gmail.com)
 * - SMTP_PORT       (contoh: 587 atau 465)
 * - SMTP_USER       (contoh: persuratan.smk@gmail.com)
 * - SMTP_PASS       (contoh: app-password 16 karakter dari Google)
 * - SMTP_CRYPTO     (contoh: tls atau ssl)
 * - SMTP_FROM_EMAIL (contoh: persuratan.smk@gmail.com)
 * - SMTP_FROM_NAME  (contoh: Persuratan SMK Muhammadiyah Bandongan)
 */

$smtp_host = getenv('SMTP_HOST') ?: (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : '');
$smtp_user = getenv('SMTP_USER') ?: (isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : '');
$smtp_pass = getenv('SMTP_PASS') ?: (isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : '');
$smtp_port = getenv('SMTP_PORT') ?: (isset($_ENV['SMTP_PORT']) ? $_ENV['SMTP_PORT'] : 587);
$smtp_crypto = getenv('SMTP_CRYPTO') ?: (isset($_ENV['SMTP_CRYPTO']) ? $_ENV['SMTP_CRYPTO'] : 'tls');

$config['protocol']     = !empty($smtp_host) ? 'smtp' : 'mail';
$config['smtp_host']    = $smtp_host;
$config['smtp_port']    = (int) $smtp_port;
$config['smtp_user']    = $smtp_user;
$config['smtp_pass']    = $smtp_pass;
$config['smtp_crypto']  = $smtp_crypto;
$config['mailtype']     = 'html';
$config['charset']      = 'utf-8';
$config['newline']      = "\r\n";
$config['crlf']         = "\r\n";
$config['wordwrap']     = TRUE;
$config['validate']     = TRUE;
