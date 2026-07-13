@echo off
REM ============================================================
REM  optimize.bat - Bikin project Laravel ini lebih ringan.
REM  Jalankan di laptop yang lemot (double-click atau via terminal).
REM  Aman diulang kapan saja.
REM ============================================================
cd /d "%~dp0"

echo.
echo [1/4] Set .env -^> APP_DEBUG=false, LOG_LEVEL=warning ...
powershell -NoProfile -Command "(Get-Content .env) -replace '^APP_DEBUG=.*','APP_DEBUG=false' -replace '^LOG_LEVEL=.*','LOG_LEVEL=warning' | Set-Content .env"

echo.
echo [2/4] Optimasi autoloader Composer ...
call composer dump-autoload --optimize

echo.
echo [3/4] Cache config, route, view ...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo [4/4] Selesai!
echo ------------------------------------------------------------
echo  CATATAN PENTING:
echo  - Kalau nanti UBAH .env / config / routes, jalankan dulu:
echo        php artisan optimize:clear
echo    lalu jalankan optimize.bat ini lagi.
echo.
echo  - Penghemat RAM TERBESAR di RAM 4GB = batasi memori SQL Server.
echo    Buka SSMS -^> klik-kanan server -^> Properties -^> Memory
echo    -^> Maximum server memory = 1024 (MB). Lihat SETUP.md.
echo.
echo  - Di XAMPP: STOP MySQL kalau tidak dipakai (kita pakai SQL Server).
echo    Jangan jalankan Apache + 'php artisan serve' bersamaan.
echo ------------------------------------------------------------
pause
