# ragpos - Localhost Performance Fixer
# Cara pakai: buka PowerShell di folder ini lalu jalankan:
#   powershell -ExecutionPolicy Bypass -File .\fix-slow-localhost.ps1

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$phpIni = 'C:\laragon\bin\php\php-7.4\php.ini'
$restartNeeded = $false

function Write-Step($text) {
    Write-Host ""
    Write-Host "== $text ==" -ForegroundColor Cyan
}

Write-Host "============================================================"
Write-Host "  RAGPOS - Localhost Performance Fixer (Laragon / artisan serve)"
Write-Host "  Project: $root"
Write-Host "============================================================"

# ---------------------------------------------------------------
# 0. Cek proses 'php artisan serve' / built-in server yang bentrok
# ---------------------------------------------------------------
Write-Step "0/6 Cek proses PHP dev server yang sedang jalan"

$phpProcs = Get-CimInstance Win32_Process -Filter "Name='php.exe'" -ErrorAction SilentlyContinue |
    Where-Object { $_.CommandLine -match 'artisan serve|-S .*server\.php' }

if ($phpProcs.Count -gt 1) {
    Write-Host "  Ditemukan $($phpProcs.Count) proses PHP dev server jalan bersamaan:" -ForegroundColor Yellow
    foreach ($p in $phpProcs) { Write-Host "    PID $($p.ProcessId): $($p.CommandLine)" }
    Write-Host "  Ini biasanya proses lama yang nyangkut (zombie) dan bikin bentrok port 8000," -ForegroundColor Yellow
    Write-Host "  request bisa nyasar ke proses yang salah / lebih lambat." -ForegroundColor Yellow
    $ans = Read-Host "  Matikan semua proses ini sekarang? Nanti jalankan ulang 'php artisan serve' secara manual (Y/N)"
    if ($ans -match '^[Yy]') {
        foreach ($p in $phpProcs) {
            Stop-Process -Id $p.ProcessId -Force -ErrorAction SilentlyContinue
        }
        Write-Host "  Proses dihentikan. Jalankan lagi: php artisan serve" -ForegroundColor Green
    } else {
        Write-Host "  Dilewati sesuai pilihan Anda."
    }
} elseif ($phpProcs.Count -eq 1) {
    Write-Host "  Cuma 1 proses dev server jalan, normal:" -ForegroundColor Green
    Write-Host "    PID $($phpProcs[0].ProcessId): $($phpProcs[0].CommandLine)"
} else {
    Write-Host "  Tidak ada proses 'php artisan serve' yang terdeteksi jalan saat ini."
}

# ---------------------------------------------------------------
# 1. OPcache (php.ini Laragon PHP 7.4 - ini yang dipakai 'php artisan serve')
# ---------------------------------------------------------------
Write-Step "1/6 Cek OPcache ($phpIni)"

if (-not (Test-Path $phpIni)) {
    Write-Host "  php.ini tidak ditemukan di $phpIni - lewati langkah ini." -ForegroundColor Yellow
} else {
    $iniContent = Get-Content -Raw $phpIni
    $opcacheLoaded = $iniContent -match '(?m)^\s*zend_extension\s*=\s*opcache'
    $opcacheEnabled = $iniContent -match '(?m)^\s*opcache\.enable\s*=\s*1'

    if ($opcacheLoaded -and $opcacheEnabled) {
        Write-Host "  OPcache sudah aktif. Lewati." -ForegroundColor Green
    } else {
        Write-Host "  OPcache TIDAK aktif. Setiap request PHP meng-compile ulang" -ForegroundColor Yellow
        Write-Host "  seluruh file Laravel dari awal -> ini biasanya penyebab utama lemot." -ForegroundColor Yellow
        $ans = Read-Host "  Aktifkan OPcache sekarang? (Y/N)"
        if ($ans -match '^[Yy]') {
            $backupPath = "$phpIni.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
            Copy-Item $phpIni $backupPath
            Write-Host "  Backup dibuat: $backupPath"

            $c = $iniContent
            if ($c -notmatch '(?m)^\s*zend_extension\s*=\s*opcache') {
                if ($c -match '(?m)^\[opcache\]') {
                    $c = $c -replace '(?m)^\[opcache\]', "zend_extension=opcache`r`n[opcache]"
                } else {
                    $c = $c + "`r`nzend_extension=opcache`r`n[opcache]`r`n"
                }
            }
            # enable_cli=1 penting: 'php artisan serve' jalan lewat CLI SAPI, kalau
            # enable_cli=0 (nilai default yg biasa dianjurkan utk Apache) OPcache tidak
            # akan pernah aktif buat server ini sama sekali.
            $settings = @{
                'opcache.enable'                = '1'
                'opcache.enable_cli'             = '1'
                'opcache.memory_consumption'     = '192'
                'opcache.interned_strings_buffer'= '16'
                'opcache.max_accelerated_files'  = '10000'
                'opcache.validate_timestamps'    = '1'
                'opcache.revalidate_freq'        = '2'
            }
            foreach ($key in $settings.Keys) {
                $val = $settings[$key]
                $pattern = "(?m)^;?\s*$([regex]::Escape($key))\s*=.*$"
                if ($c -match $pattern) {
                    $c = $c -replace $pattern, "$key=$val"
                } else {
                    $c += "`r`n$key=$val"
                }
            }
            [System.IO.File]::WriteAllText($phpIni, $c, (New-Object System.Text.UTF8Encoding($false)))
            Write-Host "  OPcache diaktifkan (validate_timestamps=1, revalidate_freq=2 -> tetap" -ForegroundColor Green
            Write-Host "  auto-detect perubahan file setiap 2 detik, aman untuk development)." -ForegroundColor Green
            $restartNeeded = $true
        } else {
            Write-Host "  Dilewati sesuai pilihan Anda."
        }
    }
}

# ---------------------------------------------------------------
# 2. Rotate laravel.log kalau sudah besar
# ---------------------------------------------------------------
Write-Step "2/6 Cek ukuran storage/logs/laravel.log"

$logPath = Join-Path $root 'storage\logs\laravel.log'
if (Test-Path $logPath) {
    $sizeMB = [math]::Round((Get-Item $logPath).Length / 1MB, 2)
    Write-Host "  Ukuran saat ini: $sizeMB MB"
    if ($sizeMB -gt 1) {
        $bakLog = "$logPath.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
        Move-Item $logPath $bakLog
        New-Item -ItemType File -Path $logPath | Out-Null
        Write-Host "  Log lama dipindah ke: $bakLog" -ForegroundColor Green
        Write-Host "  laravel.log baru dibuat kosong."
    } else {
        Write-Host "  Masih kecil, tidak perlu dirotasi."
    }
} else {
    Write-Host "  File log tidak ditemukan, lewati."
}

# ---------------------------------------------------------------
# 3. APP_DEBUG di .env
# ---------------------------------------------------------------
Write-Step "3/6 Cek APP_DEBUG di .env"

$envPath = Join-Path $root '.env'
if (Test-Path $envPath) {
    $envContent = Get-Content -Raw $envPath
    if ($envContent -match '(?m)^APP_DEBUG\s*=\s*true') {
        Write-Host "  APP_DEBUG=true -> tiap error men-generate stack trace penuh (mahal)." -ForegroundColor Yellow
        $ans = Read-Host "  Set APP_DEBUG=false untuk uji performa? (Y/N, bisa dibalik lagi nanti)"
        if ($ans -match '^[Yy]') {
            $bakEnv = "$envPath.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
            Copy-Item $envPath $bakEnv
            $newEnv = $envContent -replace '(?m)^APP_DEBUG\s*=\s*true', 'APP_DEBUG=false'
            [System.IO.File]::WriteAllText($envPath, $newEnv, (New-Object System.Text.UTF8Encoding($false)))
            Write-Host "  APP_DEBUG diset false. Backup: $bakEnv" -ForegroundColor Green
            Write-Host "  (Set lagi ke true saat sedang aktif debugging error.)"
        } else {
            Write-Host "  Dilewati sesuai pilihan Anda."
        }
    } else {
        Write-Host "  APP_DEBUG sudah bukan 'true'. Lewati."
    }
} else {
    Write-Host "  .env tidak ditemukan, lewati." -ForegroundColor Yellow
}

# ---------------------------------------------------------------
# 4. Bersihkan cache Laravel (aman, selalu reversible lewat artisan)
# ---------------------------------------------------------------
Write-Step "4/6 Bersihkan cache Laravel (config, route, view)"

$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if ($phpCmd) {
    Push-Location $root
    try {
        & php artisan config:clear
        & php artisan route:clear
        & php artisan view:clear
        & php artisan cache:clear
        Write-Host "  Cache Laravel dibersihkan." -ForegroundColor Green
    } catch {
        Write-Host "  Gagal menjalankan artisan: $_" -ForegroundColor Yellow
    } finally {
        Pop-Location
    }
} else {
    Write-Host "  'php' tidak ditemukan di PATH, lewati langkah artisan." -ForegroundColor Yellow
}

# ---------------------------------------------------------------
# 5. Optimize autoloader lewat composer (kalau tersedia)
# ---------------------------------------------------------------
Write-Step "5/6 Optimize Composer autoloader"

$composerCmd = Get-Command composer -ErrorAction SilentlyContinue
if ($composerCmd) {
    Push-Location $root
    try {
        & composer dump-autoload -o
        Write-Host "  Autoloader di-optimize." -ForegroundColor Green
    } catch {
        Write-Host "  Gagal menjalankan composer: $_" -ForegroundColor Yellow
    } finally {
        Pop-Location
    }
} else {
    Write-Host "  'composer' tidak ditemukan di PATH, lewati langkah ini." -ForegroundColor Yellow
}

# ---------------------------------------------------------------
# Ringkasan + hal yang perlu dicek manual
# ---------------------------------------------------------------
Write-Host ""
Write-Host "============================================================"
Write-Host "  SELESAI"
Write-Host "============================================================"
if ($restartNeeded) {
    Write-Host "PENTING: Hentikan proses 'php artisan serve' yang sedang jalan (Ctrl+C di" -ForegroundColor Red
    Write-Host "terminalnya, atau lihat daftar PID di langkah 0 di atas) lalu jalankan ulang" -ForegroundColor Red
    Write-Host "'php artisan serve' supaya perubahan OPcache di php.ini terpakai." -ForegroundColor Red
}
Write-Host ""
Write-Host "Perlu dicek manual (tidak diubah otomatis oleh skrip ini):"
Write-Host " - 'php artisan serve' / PHP built-in server itu SINGLE-THREADED -> cuma proses"
Write-Host "   1 request sekaligus. Halaman yang load banyak file CSS/JS terpisah akan terasa"
Write-Host "   antre satu-satu walau OPcache sudah aktif. Kalau masih terasa berat setelah ini,"
Write-Host "   pertimbangkan pindah ke Apache/Nginx bawaan Laragon (bisa proses request paralel)."
Write-Host " - DB_PORT kosong di .env (DB_HOST=localhost\MSSQLSERVER2022)."
Write-Host "   Cari port aktual instance ini lewat SQL Server Configuration"
Write-Host "   Manager > Network Configuration > TCP/IP > IP Addresses, lalu"
Write-Host "   isi DB_PORT eksplisit supaya tidak perlu resolve lewat SQL Browser tiap koneksi."
Write-Host " - routes/web.php baris 33-36 memakai DokterController yang filenya"
Write-Host "   tidak ada (app/Http/Controllers/DokterController.php hilang)."
Write-Host "   Setiap route /home, /dokter, /fertility, /facilities diakses -> 500 error"
Write-Host "   + full stack trace ke log. Kemungkinan sisa template lain, cek apakah masih dipakai."
Write-Host " - Template.blade.php memuat font/icon/script dari CDN eksternal"
Write-Host "   (Google Fonts, Ionicons, jsDelivr) - opsional di-host lokal kalau koneksi internet lambat."
Write-Host ""
