# Backend — File Management System

API Laravel untuk autentikasi, pengelolaan folder/file, departemen, trash, dan activity log.

## Requirement

- PHP 8.3 atau lebih baru
- Composer
- Database (SQLite / PostgreSQL / MySQL)

## Cara Instalasi

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

## Konfigurasi Environment

Sesuaikan file `.env`:

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
DB_CONNECTION=sqlite
```

Jika menggunakan SQLite, buat file database terlebih dahulu:

```bash
touch database/database.sqlite
```

Untuk PostgreSQL atau MySQL, ubah konfigurasi database pada file `.env`.

## Cara Menjalankan Migration & Seeder

```bash
php artisan migrate --seed
```

Untuk mereset database dan menjalankan seeder ulang:

```bash
php artisan migrate:fresh --seed
```

## Cara Menjalankan Project

```bash
php artisan serve
```

API tersedia di [http://127.0.0.1:8000](http://127.0.0.1:8000).

Apabila unggah file gagal karena batas ukuran, jalankan dengan perintah berikut:

```bash
php -d upload_max_filesize=64M -d post_max_size=64M artisan serve
```

atau:

```bash
composer serve
```

## Endpoint Utama

- `POST /api/login`
- `GET /api/me`
- `GET|POST /api/folders`, `GET|PUT|DELETE /api/folders/{id}`
- `GET|POST /api/files`, `GET|PUT|DELETE /api/files/{id}`
- `GET /api/files/{id}/download`
- `GET /api/files/{id}/preview`
- `GET|POST|PUT|DELETE /api/departments`
- `GET /api/dashboard`
- `GET /api/activity-logs`
- `GET /api/trash`
- `POST /api/trash/folders/{id}/restore`
- `POST /api/trash/files/{id}/restore`

Koleksi Postman tersedia di [`docs/postman`](../docs/postman).

## Akun Login

**Administrator**

- Email: `admin@example.com`
- Password: `password`

**Viewer**

- Email: `viewer@example.com`
- Password: `password`
