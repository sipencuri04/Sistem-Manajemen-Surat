@echo off
:: ============================================================
:: LAUNCHER MODE NORMAL (bukan kiosk/fullscreen)
:: Untuk admin TU yang ingin buka kiosk di jendela biasa
:: sambil tetap bisa akses aplikasi lain.
:: --kiosk-printing tetap aktif = cetak tanpa dialog.
:: ============================================================

set URL=https://trisnait.com/Sistem-Manajemen-Surat/index.php/antrean/kiosk

set CHROME="C:\Program Files\Google\Chrome\Application\chrome.exe"
set CHROME_ALT="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"

if exist %CHROME% (
    start "" %CHROME% --new-window --kiosk-printing --disable-print-preview --disable-translate --disable-infobars %URL%
    goto :done
)

if exist %CHROME_ALT% (
    start "" %CHROME_ALT% --new-window --kiosk-printing --disable-print-preview --disable-translate --disable-infobars %URL%
    goto :done
)

echo [ERROR] Google Chrome tidak ditemukan.
pause

:done
