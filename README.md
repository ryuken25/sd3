# Sistem Informasi Manajemen Nilai Siswa SDN 3 Mekarsari

Aplikasi web manajemen nilai siswa berbasis CodeIgniter 4 untuk SDN 3 Mekarsari. Aplikasi ini mencakup autentikasi multi-role, pengelolaan tahun ajaran, kelas, siswa, guru, mata pelajaran, KKM, input nilai, remedial, rapor, dan dashboard orang tua.

## Teknologi

- PHP 8.2 atau lebih baru
- CodeIgniter 4.7
- MySQL/MariaDB
- Composer
- PHP extensions: `intl`, `mbstring`, `mysqli`, `json`, `curl`, `gd`, `zip`

## Isi Repository

Repository ini hanya difokuskan untuk file inti program:

- `app/` - source code aplikasi, controller, model, view, migration, dan seeder.
- `public/` - document root dan aset publik.
- `writable/` - folder runtime CodeIgniter dengan file `index.html` penjaga direktori.
- `composer.json` dan `composer.lock` - dependency PHP.
- `.env` - konfigurasi localhost yang digunakan project ini.
- `README.md` - panduan instalasi.

File dokumen, diagram, archive, model AI, virtual environment, dan hasil generate tidak disertakan.

## Cara Install

### 1. Clone repository

```bash
git clone https://github.com/ryuken25/sd3.git
cd sd3
```

### 2. Install dependency PHP

```bash
composer install
```

### 3. Buat database

Buat database MySQL/MariaDB dengan nama sesuai konfigurasi default project:

```sql
CREATE DATABASE db_nilai_siswa CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

Konfigurasi database bawaan ada di `.env`:

```ini
database.default.hostname = localhost
database.default.database = db_nilai_siswa
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Jika username/password MySQL berbeda, ubah bagian tersebut di `.env`.

### 4. Jalankan migration

```bash
php spark migrate
```

### 5. Jalankan seeder data awal

Seeder utama sudah disertakan di `app/Database/Seeds`.

```bash
php spark db:seed SD3MekarsariSeeder
```

Jika butuh data khusus untuk kebutuhan capture/demo, jalankan:

```bash
php spark db:seed SD3_CapturePrepSeeder
```

### 6. Jalankan aplikasi

```bash
php spark serve
```

Buka aplikasi di browser:

```text
http://localhost:8080
```

## Login Awal

Gunakan akun yang dibuat oleh seeder. Untuk memastikan kredensial yang tersedia, cek file seeder di `app/Database/Seeds`, terutama `SD3MekarsariSeeder.php` dan seeder role terkait.

## Struktur Penting

```text
app/Config/Routes.php              Routing aplikasi
app/Controllers/                   Controller admin, guru, orang tua, dan auth
app/Database/Migrations/           Struktur tabel database
app/Database/Seeds/                Data awal aplikasi
app/Models/                        Model database
app/Views/                         Tampilan aplikasi
public/index.php                   Entry point aplikasi
writable/                          Cache, log, session, upload runtime
```

## Catatan Development

- Jangan commit folder `vendor/`; jalankan `composer install` setelah clone.
- Folder `writable/` harus bisa ditulis oleh web server.
- File `.env` pada repository ini sengaja disertakan karena hanya berisi konfigurasi localhost project.
- Dokumen seperti `.docx`, `.pdf`, diagram, hasil screenshot, model `.pt`, archive, dan virtual environment diabaikan oleh `.gitignore`.
