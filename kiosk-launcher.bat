@echo off
:: ============================================================
:: LAUNCHER KIOSK ANTREAN - SMK Muhammadiyah Bandongan
:: Jalankan file ini untuk membuka kiosk antrean secara
:: fullscreen dengan auto-print tanpa dialog cetak.
::
:: Flag --kiosk-printing = cetak langsung tanpa dialog print
:: Flag --kiosk           = fullscreen, tidak bisa minimize/close
:: ============================================================

set URL=https://trisnait.com/Sistem-Manajemen-Surat/index.php/antrean/kiosk

:: Coba Chrome dulu
set CHROME="C:\Program Files\Google\Chrome\Application\chrome.exe"
set CHROME_ALT="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"

:: Coba Edge sebagai fallback
set EDGE="C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
set EDGE_ALT="C:\Program Files\Microsoft\Edge\Application\msedge.exe"

if exist %CHROME% (
    echo Membuka Kiosk Antrean dengan Google Chrome...
    start "" %CHROME% --kiosk --kiosk-printing --disable-print-preview --disable-translate --disable-infobars --disable-session-crashed-bubble --disable-features=TranslateUI --noerrdialogs %URL%
    goto :done
)

if exist %CHROME_ALT% (
    echo Membuka Kiosk Antrean dengan Google Chrome [Alt]...
    start "" %CHROME_ALT% --kiosk --kiosk-printing --disable-print-preview --disable-translate --disable-infobars --noerrdialogs %URL%
    goto :done
)

if exist %EDGE% (
    echo Membuka Kiosk Antrean dengan Microsoft Edge...
    start "" %EDGE% --kiosk %URL% --edge-kiosk-type=fullscreen --kiosk-printing --disable-print-preview --noerrdialogs
    goto :done
)

if exist %EDGE_ALT% (
    echo Membuka Kiosk Antrean dengan Microsoft Edge [Alt]...
    start "" %EDGE_ALT% --kiosk %URL% --edge-kiosk-type=fullscreen --kiosk-printing --disable-print-preview --noerrdialogs
    goto :done
)

echo [ERROR] Chrome atau Edge tidak ditemukan di lokasi standar.
echo Silakan edit file ini dan sesuaikan path browser Anda.
pause

:done
