@echo off
:: ============================================================
:: FIX BLUETOOTH PORTS & SPOOLER - SMK MUHAMMADIYAH BANDONGAN
:: Mengatur baud rate 9600,n,8,1 dan mematikan hardware handshake
:: ============================================================

net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [INFO] Meminta hak akses Administrator...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

echo Menghapus flag hardware handshake pada port COM3 sampai COM10...
for /L %%i in (3,1,10) do (
    reg add "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Ports" /v "COM%%i:" /t REG_SZ /d "9600,n,8,1" /f
)

echo Merestart service Print Spooler...
net stop spooler
net start spooler

echo.
echo ============================================================
echo [SUKSES] Port Bluetooth COM3 - COM10 sudah di-fix 9600,n,8,1!
echo ============================================================
echo.
pause

