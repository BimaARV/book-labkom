# UPDATE PRD FINAL – ARSITEKTUR PENGEMBANGAN

## Pendekatan Pengembangan

Sistem **Labkom Booking System** wajib dikembangkan menggunakan pendekatan **Full Stack Development** dengan memanfaatkan ekosistem Laravel secara menyeluruh.

Seluruh fitur, baik frontend maupun backend, harus berada dalam **satu codebase Laravel 13 (Monolithic Architecture)** untuk mempermudah proses maintenance, deployment, serta pengembangan lanjutan oleh tim internal Techub.

---

## Arsitektur Sistem

### Arsitektur yang Digunakan

**Laravel 13 Full Stack Monolith**

Arsitektur terdiri dari:

1. Presentation Layer
2. Application Layer
3. Domain Layer
4. Data Layer
5. Infrastructure Layer

---

## Teknologi yang Digunakan

| Layer            | Teknologi                 |
| ---------------- | ------------------------- |
| Backend          | Laravel 13                |
| Frontend         | Laravel Blade             |
| UI Framework     | Bootstrap (versi terbaru) |
| JavaScript       | Vanilla JavaScript        |
| Authentication   | Laravel Breeze            |
| Database         | MariaDB                   |
| WhatsApp Gateway | Baileys                   |
| Email Service    | SMTP / Sendmail           |
| Queue            | Laravel Queue             |
| Scheduler        | Laravel Scheduler         |
| Containerization | Docker                    |
| Reverse Proxy    | Nginx                     |

---

## Frontend Development

Frontend wajib dikembangkan menggunakan:

* Laravel Blade
* Bootstrap
* JavaScript
* AJAX (jika diperlukan)
* Alpine.js (opsional)

Frontend harus bersifat:

* Responsive.
* Mobile-friendly.
* Mudah digunakan.
* Konsisten dengan identitas visual Techub.

---

## Backend Development

Backend wajib menggunakan Laravel 13 dengan menerapkan praktik terbaik Laravel, meliputi:

* MVC Pattern.
* Form Request Validation.
* Service Layer.
* Event & Listener.
* Queue Job.
* Notification System.
* Environment Configuration.
* Laravel Scheduler.
* Migration-based Database Management.

---

## Database Development

Seluruh struktur database wajib menggunakan:

* Laravel Migration.
* Laravel Seeder.
* Eloquent ORM.

Database engine yang digunakan adalah:

MariaDB.

---

## Integrasi Eksternal

### WhatsApp Gateway

Menggunakan:

Baileys.

Fitur:

* Kirim notifikasi ke Group Admin.
* Kirim notifikasi ke User.
* Group ID dapat diubah melalui panel admin.
* Nomor gateway dapat diubah melalui panel admin.

---

### Email Notification

Menggunakan:

SMTP / Sendmail.

Konfigurasi melalui panel admin:

* SMTP Host.
* SMTP Port.
* SMTP Username.
* SMTP Password.
* Encryption.
* Email From.
* CC Email.

CC default:

* noc
* info

---

## Docker Deployment

Sistem wajib dapat dijalankan menggunakan Docker.

Container minimal:

1. nginx
2. app (Laravel)
3. mariadb
4. queue-worker
5. scheduler
6. baileys-service

Developer wajib menyediakan:

* Dockerfile.
* docker-compose.yml.
* Dokumentasi deployment.

---

## Batasan Pengembangan

Untuk menjaga konsistensi arsitektur dan mempermudah maintenance oleh tim internal Techub, pengembangan **tidak diperbolehkan menggunakan arsitektur frontend-backend terpisah**.

### Diperbolehkan

✓ Laravel Blade

✓ Bootstrap

✓ JavaScript

✓ AJAX

✓ Alpine.js

---

### Tidak Diperbolehkan

✗ React SPA

✗ Next.js

✗ Vue SPA

✗ Nuxt.js

✗ Angular

✗ Backend API terpisah dari Laravel utama

---

## Alasan Pemilihan Arsitektur

Pendekatan Laravel Full Stack dipilih karena:

* Lebih mudah dipelihara oleh tim IT Infrastructure.
* Mempercepat proses pengembangan.
* Mempermudah proses deployment menggunakan Docker.
* Mengurangi kompleksitas integrasi.
* Cocok untuk aplikasi internal perusahaan.
* Selaras dengan kebutuhan Laravel Breeze.
* Mengurangi biaya operasional pengembangan.

---

## Deliverables Teknis

Developer wajib menyerahkan:

* Source Code Laravel 13 Full Stack.
* Dockerfile.
* Docker Compose.
* Migration Database.
* Seeder Database.
* Dokumentasi Instalasi.
* Dokumentasi User.
* Dokumentasi Admin.
* Panduan Backup & Restore.
* File ZIP hasil proyek.

---

## Acceptance Criteria

Sistem dinyatakan memenuhi standar pengembangan apabila:

* Seluruh fitur berjalan dalam satu aplikasi Laravel 13.
* Tidak terdapat framework frontend terpisah.
* Docker deployment berjalan dengan baik.
* WhatsApp notification berjalan menggunakan Baileys.
* Email notification berjalan menggunakan SMTP.
* Seluruh migration dapat dijalankan menggunakan artisan migrate.
* Seluruh fitur PRD dapat digunakan tanpa bug kritis.
