# Deploy Laravel ke Render

Panduan ini memakai Render Web Service dengan Docker dan database MySQL eksternal.

## 1. Siapkan Database MySQL

Gunakan salah satu:

- Aiven MySQL
- Railway MySQL
- MySQL lain yang bisa diakses dari internet

Simpan credential berikut:

- host
- port
- database
- username
- password

## 2. Test Database dari Lokal

Ubah `.env` lokal sementara ke database cloud, lalu jalankan:

```bash
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
```

## 3. Push ke GitHub

Pastikan file berikut ikut ter-push:

- `Dockerfile`
- `docker/apache.conf`
- `docker/start.sh`
- `.dockerignore`

Jangan push `.env`.

## 4. Buat Web Service di Render

1. Buka Render Dashboard.
2. Pilih **New > Web Service**.
3. Connect repository GitHub project ini.
4. Pilih runtime/environment **Docker**.
5. Pilih branch utama.
6. Deploy.

## 5. Environment Variables di Render

Isi minimal:

```env
APP_NAME=Pemantauan Sikap
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:isi_dari_php_artisan_key_generate_show
APP_URL=https://nama-service.onrender.com

DB_CONNECTION=mysql
DB_HOST=host_database
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=host_smtp
MAIL_PORT=587
MAIL_USERNAME=username_smtp
MAIL_PASSWORD=password_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email_pengirim
MAIL_FROM_NAME=Pemantauan Sikap
```

Generate `APP_KEY` dari lokal:

```bash
php artisan key:generate --show
```

## 6. Migrasi Otomatis Opsional

Jika ingin migrasi dijalankan otomatis saat container start, tambahkan:

```env
RUN_MIGRATIONS=true
```

Untuk awal deploy boleh aktifkan. Setelah database stabil, lebih aman matikan lagi dan jalankan migrasi secara sadar dari lokal/CI.

## 7. Health Check

Gunakan path:

```text
/up
```

## 8. Catatan Render Free

Instance free bisa sleep saat tidak aktif. Request pertama setelah sleep bisa lebih lambat.
