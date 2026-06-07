@echo off
REM ============================================================================
REM  SD3 Mekarsari — Installer Windows
REM  One-shot setup: cek XAMPP, install deps, create DB, migrate, seed.
REM
REM  Pakai:  ./setup-windows.bat            (full setup, asumsi DB sudah ada/baru)
REM          ./setup-windows.bat reset      (DROP DB dulu, lalu setup ulang fresh)
REM
REM  Tested: Windows 11, PHP 8.2, XAMPP MySQL 8.0+/MariaDB 10.4+
REM ============================================================================

setlocal EnableDelayedExpansion
cd /d "%~dp0"
chcp 65001 >nul

set "XAMPP=C:\xampp"
set "MYSQL_EXE=%XAMPP%\mysql\bin\mysql.exe"
set "MYSQLD_EXE=%XAMPP%\mysql\bin\mysqld.exe"
set "DB_USER=root"
set "DB_PASS="
set "DB_NAME=db_nilai_siswa"
set "MODE=%1"

echo.
echo ==========================================
echo  SD3 Mekarsari Setup Windows
echo ==========================================
echo.

REM --- 1) Cek MySQL ada ------------------------------------------------------
if not exist "%MYSQL_EXE%" (
    echo [X] mysql.exe tidak ditemukan di %MYSQL_EXE%
    echo     Pastikan XAMPP ter-install di C:\xampp atau ubah path di file ini.
    exit /b 1
)

REM --- 2) Start mysqld kalau belum jalan -------------------------------------
echo [1/6] Memastikan MySQL berjalan...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if errorlevel 1 (
    echo       MySQL mati, starting mysqld...
    start "" /B "%MYSQLD_EXE%"
    timeout /t 5 /nobreak >nul
) else (
    echo       MySQL sudah jalan.
)

REM --- 3) Composer install kalau vendor/ belum ada ---------------------------
echo [2/6] Memeriksa dependencies PHP (vendor/)...
if not exist "vendor\autoload.php" (
    where composer >nul 2>nul
    if errorlevel 1 (
        echo [X] composer tidak ditemukan di PATH. Install dulu: https://getcomposer.org
        exit /b 1
    )
    echo       vendor/ kosong, jalankan composer install...
    call composer install --no-dev --optimize-autoloader
    if errorlevel 1 (
        echo [X] composer install gagal.
        exit /b 1
    )
) else (
    echo       vendor/ sudah ada.
)

REM --- 4) Buat / Reset DB ---------------------------------------------------
echo [3/6] Menyiapkan database %DB_NAME%...
if /I "%MODE%"=="reset" (
    echo       MODE=reset, DROP DATABASE %DB_NAME% dulu...
    "%MYSQL_EXE%" -u %DB_USER% -e "DROP DATABASE IF EXISTS `%DB_NAME%`;"
    if errorlevel 1 ( echo [X] gagal drop database & exit /b 1 )
)
"%MYSQL_EXE%" -u %DB_USER% -e "CREATE DATABASE IF NOT EXISTS `%DB_NAME%` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
if errorlevel 1 ( echo [X] gagal create database & exit /b 1 )
echo       OK: database %DB_NAME% siap.

REM --- 5) Migrate ------------------------------------------------------------
echo [4/6] Menjalankan migration...
call php spark migrate
if errorlevel 1 (
    echo [X] migration gagal. Cek pesan error di atas.
    exit /b 1
)

REM --- 6) Seeder utama -------------------------------------------------------
echo [5/6] Menjalankan seeder SD3MekarsariSeeder...
call php spark db:seed SD3MekarsariSeeder
if errorlevel 1 (
    echo [!] seeder utama gagal. Lanjut tanpa seed.
)

REM --- 7) Verifikasi ---------------------------------------------------------
echo [6/6] Verifikasi jumlah tabel...
"%MYSQL_EXE%" -u %DB_USER% -D %DB_NAME% -e "SELECT COUNT(*) AS jumlah_tabel FROM information_schema.tables WHERE table_schema = '%DB_NAME%';"
"%MYSQL_EXE%" -u %DB_USER% -D %DB_NAME% -e "SELECT COUNT(*) AS rows_nilai FROM nilai;" 2>nul

echo.
echo ==========================================
echo  SELESAI! Jalankan:  php spark serve
echo  Buka:                http://localhost:8080
echo ==========================================
echo.

endlocal
