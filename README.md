# File Management System

Aplikasi web untuk mengelola folder dan file berdasarkan departemen. Fitur utama meliputi struktur folder bertingkat, unggah file, pratinjau, unduh, trash (soft delete), serta activity log.

## Struktur Project

| Folder | Keterangan |
| --- | --- |
| [`backend/`](./backend) | API Laravel + Sanctum |
| [`frontend/`](./frontend) | Aplikasi Vue 3 (Vite) |
| [`docs/postman/`](./docs/postman) | Koleksi Postman untuk pengujian API |

Dokumentasi instalasi dan konfigurasi tersedia di masing-masing folder:

- [README Backend](./backend/README.md)
- [README Frontend](./frontend/README.md)

## Role Pengguna

- **Administrator** — akses penuh (CRUD, trash, activity log)
- **Viewer** — hanya dapat melihat dan mengunduh file

## Cara Menjalankan Singkat

```bash
# Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve

# Frontend (terminal baru)
cd frontend
pnpm install
pnpm dev
```

Buka aplikasi di [http://localhost:5173](http://localhost:5173).

## Akun Login

**Administrator**

- Email: `admin@example.com`
- Password: `password`

**Viewer**

- Email: `viewer@example.com`
- Password: `password`
