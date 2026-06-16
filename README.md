# Techub Laravel Booking System

Sistem manajemen pemesanan Labkom berbasis Laravel. Aplikasi ini telah disiapkan untuk _Production-Ready Docker Deployment_.

## Persiapan Server

1. **Requirement Server**:
   - OS Linux (Ubuntu/Debian direkomendasikan).
   - Minimal 2GB RAM & 20GB Storage.
2. **Instalasi Docker Engine**:
   - Jalankan `curl -fsSL https://get.docker.com | sh`
3. **Instalasi Docker Compose**:
   - Sudah ter-bundle dengan versi Docker Engine terbaru (jalankan `docker compose version` untuk mengecek).

## Deployment

1. **Clone Repository**:
   ```bash
   git clone <repo-url> book-labkom
   cd book-labkom
   ```
2. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   # Sesuaikan pengaturan database di .env (DB_PASSWORD dsb.)
   ```
3. **Build Image**:
   ```bash
   docker compose build
   ```
4. **Menjalankan Container**:
   ```bash
   docker compose up -d
   ```
5. **Generate APP_KEY & Storage Link**:
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan storage:link
   ```
6. **Migration Database**:
   ```bash
   docker compose exec app php artisan migrate --force
   ```
   *(Secara default, container `app` akan mencoba menjalankan migration saat dihidupkan via `entrypoint.sh`)*
7. **Seeder Database** *(opsional jika baru pertama kali)*:
   ```bash
   docker compose exec app php artisan db:seed --force
   ```

## Operasional

- **Start Container**: `docker compose start`
- **Stop Container**: `docker compose stop`
- **Restart Container**: `docker compose restart`
- **Rebuild Container**: `docker compose up -d --build` (Gunakan perintah ini setiap kali ada perubahan pada source code PHP atau composer dependencies).
- **Update Aplikasi (Source Code / Git Pull)**:
  Setelah `git pull`, lakukan rebuild:
  ```bash
  docker compose build
  docker compose up -d
  ```
- **Update Dependency**:
  Ubah file composer, lalu jalankan `docker compose build` agar dependencies ditarik ulang ke dalam image.
- **Backup Database**:
  ```bash
  docker compose exec mariadb sh -c 'exec mysqldump --all-databases -u root -p"$MARIADB_ROOT_PASSWORD"' > backup.sql
  ```
- **Restore Database**:
  ```bash
  cat backup.sql | docker compose exec -T mariadb sh -c 'exec mysql -u root -p"$MARIADB_ROOT_PASSWORD"'
  ```
- **Melihat Log**:
  - Semua service: `docker compose logs -f`
  - Spesifik service (contoh: Nginx): `docker compose logs -f nginx`
  - Log Laravel tersimpan pada: `storage/logs/laravel.log` (Bisa diakses dari dalam container atau diakses dari host jika dibind).

## File Synchronization & Update Strategy

- **Bind Mount vs Rebuild**:
  Aplikasi ini berjalan dalam mode production. Source code disalin (di-copy) ke dalam container saat proses `build`.
  Oleh karena itu:
  - **JANGAN** menggunakan bind-mount untuk mengubah source code di production secara live.
  - Setiap kali ada **perubahan kode**, Anda **WAJIB** menjalankan `docker compose build && docker compose up -d`.
- **Kapan Harus Restart?**
  Gunakan `docker compose restart` hanya jika container mengalami hang, Anda mengubah konfigurasi database manual di luar sistem, atau jika Nginx butuh reload.

## Troubleshooting

- **Permission Issue (storage/cache)**:
  Jalankan `docker compose exec app chown -R www-data:www-data storage bootstrap/cache`
- **Database Connection Issue**:
  Pastikan variable `.env` `DB_HOST=mariadb` dan kredensial cocok dengan environment di container mariadb.
- **APP_KEY Issue**:
  Jika log menunjukkan error decryption, jalankan `docker compose exec app php artisan key:generate` lalu restart container `app`.
- **Container Unhealthy**:
  Gunakan `docker inspect --format "{{json .State.Health }}" labkom_app` untuk melihat log healthcheck.
- **Queue/Scheduler Issue**:
  Buka log worker dengan `docker compose logs queue` atau `docker compose logs scheduler` untuk mengecek error yang dialami.
- **Nginx Error 502 Bad Gateway**:
  Berarti container `app` belum siap. Tunggu beberapa detik karena Nginx menunggu proses startup PHP-FPM di container `app`.
