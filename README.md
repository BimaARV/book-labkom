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
   Karena file `.env` tidak dimasukkan ke dalam container demi keamanan, generate key dengan memunculkannya di layar:
   ```bash
   docker compose exec app php artisan key:generate --show
   ```
   *Copy* teks `base64:...` yang muncul, buka file `.env` di host, lalu *paste* ke baris `APP_KEY=`. Setelah itu perbarui container:
   ```bash
   docker compose up -d
   ```
   Lalu jalankan pembuatan *storage link*:
   ```bash
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
   
*(Catatan: WhatsApp Gateway/Baileys sudah otomatis tergabung di dalam `docker-compose.yml` dan akan menyala bersamaan dengan aplikasi utama di port `3000` tanpa perlu perintah tambahan).*

## Operasional

- **Start Container**: `docker compose start`
- **Stop Container**: `docker compose stop`
- **Restart Container**: `docker compose restart`
- **Rebuild Container**: `docker compose up -d --build` (Gunakan perintah ini setiap kali ada perubahan pada source code PHP atau composer dependencies).
- **Update Aplikasi (Source Code / Git Pull)**:
  Aplikasi berjalan tanpa bind-mount, sehingga setiap ada pembaruan kode (termasuk kode Laravel maupun kode WhatsApp-Service), Anda wajib merakit ulang image-nya.
  Langkah update (tanpa perlu mematikan aplikasi/zero-downtime):
  ```bash
  git pull origin main
  docker compose build
  docker compose up -d
  ```
  *(Catatan: Anda tidak perlu menjalankan `docker compose down`. Perintah di atas akan me-rebuild di background, lalu me-restart seluruh container dengan versi terbaru secara otomatis).*
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

## Konfigurasi URL & WhatsApp Gateway (Dashboard Admin)

Pengaturan **App URL** dan **WhatsApp Gateway URL** dapat diubah kapan saja secara dinamis langsung dari **Dashboard Admin > Settings** tanpa perlu mengedit *source code* atau memulai ulang *container*.
Namun, ada aturan penting (terkait keamanan *Mixed Content* di browser) yang **WAJIB** diikuti saat mengisi URL:

1. **Akses via IP (Tahap Uji Coba):**
   Jika aplikasi belum ditautkan ke domain (masih menggunakan HTTP), Anda punya dua pilihan URL WhatsApp:
   - Akses Langsung: `http://<IP_SERVER>:3000`
   - Via Rute Nginx: `http://<IP_SERVER>:8000/whatsapp` *(Disarankan, agar Nginx yang mengarahkan rutenya).*

2. **Akses via Domain HTTPS (Production):**
   Jika aplikasi sudah live dan menggunakan domain ber-SSL/Gembok Hijau (contoh: `https://labkom.domain.com`), maka Anda **TIDAK BOLEH** mengisi WhatsApp Gateway dengan URL IP (`http://...`). Hal ini akan diblokir paksa oleh browser (Chrome/Firefox).
   Karena kita sudah membuat "pintu rahasia" di Nginx, Anda cukup menggunakan **satu domain yang sama** untuk keduanya:
   - **App URL**: `https://labkom.domain.com`
   - **WhatsApp Gateway URL**: `https://labkom.domain.com/whatsapp`

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
