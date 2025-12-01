# Laporan Internal API (Backend Only)

Backend Laravel (DDD) untuk Sistem Laporan Internal Lapangan dengan persetujuan Kabid via TTE (mock). Termasuk migrasi, model, policy, services, jobs, presigned S3/MinIO, validasi EXIF GPS, PDF ringkasan, OpenAPI, dan Docker compose.

## 📚 Dokumentasi API

Dokumentasi API dapat diakses melalui Postman:
[Postman Documentation](https://documenter.getpostman.com/view/2932185/2sAYkEpK7C)

---

## 🚀 Fitur

- RBAC sederhana (unit + role), policy Report
- Presigned upload (S3/MinIO), validasi EXIF GPS + akurasi, checksum SHA-256, pHash
- Jobs: PDF (Dompdf) + QR, Sign (TTE mock), Antivirus scan (stub), Thumbnail
- OpenAPI di `docs/openapi.yaml`, health `/api/health`, metrics `/api/metrics`
- Export CSV/XLSX (Maatwebsite/Excel) siap dipakai di service terpisah

---

## ⚡ Laravel Octane & FrankenPHP

Laravel Octane meningkatkan performa API dengan menggantikan **PHP-FPM** standar dengan **FrankenPHP**.

### **FrankenPHP Mode**

- Berbasis **Caddy server** dengan optimalisasi untuk Laravel.
- Bisa berjalan sebagai **standalone server** tanpa perlu PHP-FPM.
- Mendukung auto-reloading dan HTTP/3 untuk performa lebih tinggi.
- Ideal untuk **Dockerized Laravel API** dengan container ringan.

### **Menjalankan Laravel Octane dengan FrankenPHP**

1️⃣ **Install FrankenPHP**

```sh
composer require dunglas/frankenphp
```

2️⃣ **Jalankan dengan FrankenPHP**

```sh
php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000
```

Dengan menggunakan **Laravel Octane & FrankenPHP**, API Desa bisa berjalan lebih **ringan**, **cepat**, dan **efisien** dalam lingkungan produksi berbasis Docker.

---

## 🔄 Endpoint API

Kontrak API: `docs/openapi.yaml`

---

## 🛠️ Menjalankan (Dev)

1. `cp .env.example .env` lalu sesuaikan bila perlu
2. `docker compose -f docker-compose.yml -f docker-compose.override.yml up -d postgres redis minio`
3. `composer install`
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan storage:link`
7. Jalankan worker: `php artisan queue:work`

Prod (FrankenPHP+Caddy) sesuai `docker-compose.yml` default; siapkan Postgres/Redis/MinIO terkelola dan set variabel `.env`.

---

## TTE

- Default `TTE_PROVIDER=mock` menggunakan adapter `Infra\Report\Signing\MockTteSigner`.
- Untuk BSrE, implement adapter `TteSignerInterface` baru dan binding di `TteServiceProvider`.

## Akseptansi & Catatan

- Evidence wajib foto (JPEG/PNG/HEIC) via presigned; server memverifikasi EXIF GPS dan akurasi ≤ ambang (`EVIDENCE_MAX_ACCURACY`).
- Approve memicu PDF ringkasan + tanda tangan (mock) dan metadata signature.
- Evidence immutable setelah approved (diterapkan via policy dan state machine; endpoint hapus tidak disediakan).
