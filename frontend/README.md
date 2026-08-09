# Frontend — File Management System

Antarmuka pengguna berbasis Vue 3 untuk mengelola folder dan file melalui API backend.

## Requirement

- Node.js 18 atau lebih baru
- pnpm atau npm
- Backend API yang sudah berjalan di `http://localhost:8000`

## Cara Instalasi

```bash
cd frontend
pnpm install
```

Apabila menggunakan npm, jalankan `npm install`.

## Konfigurasi Environment

Frontend terhubung ke API pada alamat:

```text
http://localhost:8000/api
```

Konfigurasi tersebut berada di `src/lib/api.js`. Pastikan backend sudah berjalan sebelum membuka aplikasi.

## Cara Menjalankan Project

```bash
pnpm dev
```

Aplikasi tersedia di [http://localhost:5173](http://localhost:5173).

Untuk membangun versi production:

```bash
pnpm build
pnpm preview
```

## Halaman Utama

- Login
- Dashboard
- Documents (folder dan file)
- File detail (preview dan download)
- Departments (Administrator)
- Activity Log (Administrator)
- Trash (Administrator)

## Akun Login

**Administrator**

- Email: `admin@example.com`
- Password: `password`

**Viewer**

- Email: `viewer@example.com`
- Password: `password`

Dokumentasi backend tersedia di [README Backend](../backend/README.md).
