Table users {
id bigint [pk]
name varchar
email varchar [unique]
password varchar
phone varchar
created_at timestamp
updated_at timestamp
}

Table tenants {
id bigint [pk]
user_id bigint [unique, not null]  // fk -> users.id, cascade on delete
ktp_number varchar [unique]
emergency_name varchar [nullable]
emergency_contact varchar [nullable]
job varchar [nullable]
created_at timestamp
updated_at timestamp
}

Table rooms {
id bigint [pk]
room_number varchar [unique]
floor varchar [nullable]
price decimal(12,2)              // nama kolom sebenarnya: price (bukan price_per_month)
status enum(available, occupied, maintenance) [default: available]
is_active boolean [default: true]
description text [nullable]
image varchar [nullable]         // path gambar, disimpan via storage disk 'public'
created_at timestamp
updated_at timestamp
}

Table facilities {
id bigint [pk]
name varchar
description text
created_at timestamp
updated_at timestamp
}

Table room_facilities {
id bigint [pk]
room_id bigint
facility_id bigint
created_at timestamp
updated_at timestamp
}

Table leases {
id bigint [pk]
tenant_id bigint [nullable]      // fk -> tenants.id, cascade on delete
room_id bigint                   // fk -> rooms.id, cascade on delete
start_date date
end_date date [nullable]
checkout_date date [nullable]
m_price decimal(12,2)            // nama kolom sebenarnya: m_price (bukan monthly_price)
deposit_amount decimal(12,2) [default: 0]
deposit_deduction decimal(12,2) [default: 0]
deposit_refunded decimal(12,2) [default: 0]
status enum(pending, active, completed, cancelled) [default: active]
room_condition varchar [nullable]
note text [nullable]
checkout_notes text [nullable]
renewal_count int [default: 0]
last_renewed_at datetime [nullable]
created_at timestamp
updated_at timestamp
}

Table payments {
id bigint [pk]
lease_id bigint
invoice_number varchar [unique]
amount decimal(12,2)

billing_period date
due_date date
payment_date timestamp

payment_method enum(cash, e_wallet, bank_tf, qris) [default: cash]
status enum(paid, pending, unpaid, overdue) [default: unpaid]

proof_img varchar [nullable]     // nama kolom sebenarnya: proof_img (bukan proof_image)

verified_by bigint [nullable]    // fk -> users.id, null on delete

notes text

created_at timestamp
updated_at timestamp
}

Table maintenance_requests {
id bigint [pk]

room_id bigint
tenant_id bigint

title varchar
description text

image_path varchar

priority varchar
status varchar

cost decimal(12,2)

handled_by bigint

reported_at timestamp
resolved_at timestamp

created_at timestamp
updated_at timestamp
}

Table expenses {
id bigint [pk]

title varchar
description text [nullable]

amount decimal(12,2) [default: 0]

expense_date date [nullable]

user_id bigint                   // fk -> users.id, cascade on delete (ERD lama: created_by)

created_at timestamp
updated_at timestamp
}

Ref: tenants.user_id > users.id

Ref: room_facilities.room_id > rooms.id
Ref: room_facilities.facility_id > facilities.id

Ref: leases.tenant_id > tenants.id
Ref: leases.room_id > rooms.id

Ref: payments.lease_id > leases.id
Ref: payments.verified_by > users.id

Ref: maintenance_requests.room_id > rooms.id
Ref: maintenance_requests.tenant_id > tenants.id
Ref: maintenance_requests.handled_by > users.id

Ref: expenses.user_id > users.id

# 🏠 KosFly - Sistem Manajemen Kos

KosFly adalah aplikasi berbasis web untuk membantu pengelolaan operasional rumah kos secara digital. Sistem ini dibuat untuk mempermudah pemilik kos, penjaga kos, dan penyewa dalam mengelola kamar, penyewaan, pembayaran, fasilitas, laporan kerusakan, serta administrasi kos.

Project ini dibangun menggunakan **Laravel Framework** dengan database **MySQL**.

---

## ✨ Fitur Utama

### 🔐 Authentication & Authorization

- Login dan registrasi menggunakan Laravel Breeze
- Sistem role menggunakan Spatie Laravel Permission
- 3 role utama:
    - **Admin**
        - Mengelola seluruh sistem
        - Mengelola user
        - Verifikasi pembayaran
        - Melihat laporan keuangan

    - **Owner (Pemilik Kos)**
        - Memantau seluruh data (read-only)
        - Melihat kamar, penghuni, kontrak, pembayaran, maintenance, pengeluaran, laporan
        - Tidak dapat membuat, mengubah, atau menghapus data

    > Catatan: role `caretaker` (penjaga kos) yang disebut pada dokumentasi lama **tidak ada**
    > di database. Role yang benar-benar diseed (`RoleSeeder`) adalah `admin`, `owner`, `tenant`.
    > Registrasi publik otomatis memberi role `tenant`. User **tanpa role** ditolak (403)
    > dari seluruh area pengelola oleh `RoleMiddleware`.

    - **Tenant (Penyewa)**
        - Melihat informasi kamar
        - Melihat tagihan
        - Melakukan pembayaran
        - Membuat laporan kerusakan

---

## 🛠️ Teknologi yang Digunakan

### Backend

- PHP 8.3
- Laravel 13
- Laravel Breeze
- Spatie Laravel Permission

### Database

- MySQL

### Development Environment

- Laragon
- Composer
- Node.js & NPM

---

## 🚀 Cara Menjalankan Project (Setup Lokal)

Prasyarat: PHP 8.3+, Composer, Node.js & NPM, dan MySQL (Laragon sudah menyertakan semuanya).

```bash
# 1. Install dependency PHP dan Node
composer install
npm install

# 2. Siapkan environment
cp .env.example .env
php artisan key:generate
```

Buka `.env` dan pastikan koneksi database sesuai. Default yang dipakai proyek ini:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=manajemenkos
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `manajemenkos` di MySQL terlebih dahulu (bisa lewat HeidiSQL/phpMyAdmin bawaan Laragon),
lalu jalankan migrasi beserta seeder:

```bash
# 3. Buat tabel + isi role dan akun awal (admin & owner)
php artisan migrate --seed

# 4. Symlink storage — WAJIB, kalau tidak gambar tidak akan tampil
php artisan storage:link

# 5. Build asset frontend
npm run build

# 6. Jalankan server
php artisan serve
```

> **Peringatan:** `php artisan migrate:fresh` akan **menghapus seluruh tabel dan data**.
> Jangan dijalankan pada database yang sudah berisi data penting.

### Akun Bawaan (hasil seeder)

| Role | Email | Password |
|---|---|---|
| Admin | `admin@gmail.com` | `password` |
| Owner | `owner@gmail.com` | `password` |

Ganti password akun-akun ini sebelum dipakai di lingkungan nyata.

### Menjalankan Test

```bash
php artisan test
```

Test berjalan di atas SQLite in-memory (lihat `phpunit.xml`), jadi tidak menyentuh database MySQL Anda.

---

# 🚀 Deploy ke Railway

Deploy memakai builder bawaan Railway (**Railpack**). Tidak ada `Dockerfile`.

> **`Procfile` sudah dihapus.** Perintah lamanya memakai `php artisan serve`
> (dev server, satu worker) yang akan menimpa FrankenPHP. Procfile juga sudah
> dideprekasi oleh Railpack.

## Yang sudah ditangani Railpack secara otomatis

Railpack mengenali aplikasi Laravel dari file `artisan`, lalu otomatis:

| Otomatis | Keterangan |
|---|---|
| Document root | diarahkan ke `public/` |
| Web server | **FrankenPHP**, bukan `php artisan serve` |
| `npm run build` | dijalankan karena `package.json` terdeteksi, lalu dependency dev dibuang |
| `php artisan storage:link` | symlink `public/storage` dibuat saat container start |
| Cache produksi | config, event, route, dan view cache dibuat saat build/start |

Jadi **tidak perlu** menulis perintah build atau start khusus untuk service App.

## Arsitektur service

| Service | Peran | Domain publik |
|---|---|---|
| **App** | melayani HTTP | ✅ ya |
| **Cron** | menjalankan scheduler (tagihan bulanan otomatis) | ❌ tidak |
| **MySQL** | database | ❌ tidak |

## 1. Service App

Tambahkan dari dashboard: **New → GitHub Repo**, lalu isi **Variables**:

| Variable | Nilai | Kenapa penting |
|---|---|---|
| `APP_NAME` | `KosFly` | |
| `APP_ENV` | `production` | **wajib** |
| `APP_DEBUG` | `false` | **wajib** — kalau tidak, stack trace dan isi `.env` bocor ke publik |
| `APP_KEY` | hasil `php artisan key:generate --show` | **wajib** — tanpa ini aplikasi error |
| `APP_URL` | `https://<domain-app-anda>` | dipakai untuk URL gambar, invoice, dan link WhatsApp |
| `LOG_CHANNEL` | `stderr` | filesystem ephemeral, log ke disk akan hilang |
| `LOG_STDERR_FORMATTER` | `\Monolog\Formatter\JsonFormatter` | log terstruktur di Railway |
| `DB_URL` | `${{MySQL.MYSQL_URL}}` | `config/database.php` sudah membaca `DB_URL` |
| `RAILPACK_SKIP_MIGRATIONS` | `true` | lihat catatan migration di bawah |
| `RAILPACK_PHP_EXTENSIONS` | `fileinfo,pdo_mysql` | dipakai aplikasi tapi belum dideklarasikan di `composer.json` |
| `MAIL_MAILER` / `RESEND_API_KEY` | bila email dipakai | `ResendService` melempar exception kalau key masih placeholder |
| `MIDTRANS_SERVER_KEY` / `MIDTRANS_CLIENT_KEY` | `SB-Mid-server-...` (sandbox) | kosong = pembayaran online otomatis mati, transfer manual tetap jalan |

`SESSION_DRIVER`, `CACHE_STORE`, dan `QUEUE_CONNECTION` sudah default ke `database` di `config/`, jadi cukup diisi kalau ingin eksplisit.

Alternatif selain `DB_URL` (bila ingin memisahkan tiap nilai):

```
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

### Pre-Deploy Command

Railpack secara default menjalankan migration otomatis saat startup — dokumentasinya juga menyebut *seeding*. Karena proyek ini punya akun seeder berpassword lemah (`admin@gmail.com` / `password`), seeding otomatis di produksi tidak diinginkan. Karena itu:

1. Set `RAILPACK_SKIP_MIGRATIONS=true`, dan
2. jalankan migration secara eksplisit lewat **Settings → Deploy → Pre-Deploy Command**:

```bash
chmod +x ./railway/init-app.sh && sh ./railway/init-app.sh
```

> `chmod +x` ditulis di dalam perintah karena izin executable tidak tersimpan
> saat repo di-commit dari Windows.

### 🔴 Volume — WAJIB untuk fitur gambar

Filesystem container Railway **ephemeral**: seluruh isi `storage/` kosong lagi setiap deploy. Tanpa volume, semua foto kamar, bukti transfer, dan foto maintenance hilang, sementara baris di database tetap ada dan menunjuk file yang sudah tidak ada.

Buat volume (**New → Volume**) dan mount pada:

```
/app/storage/app/public
```

Jalur `/app` adalah lokasi aplikasi di image Railpack.

## 2. Service Cron

Buat service **kedua** dari repo yang sama (Railpack tidak menjalankan scheduler), lalu:

- **Variables**: sama persis dengan service App.
- **Settings → Deploy → Custom Start Command**:

```bash
chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh
```

Service ini yang membuat `kos:generate-monthly-bills` (terjadwal tanggal 1 pukul 00:05 di `routes/console.php`) benar-benar berjalan.

> **Queue worker belum diperlukan.** Tidak ada `dispatch()` maupun job
> `ShouldQueue` di aplikasi ini, jadi service worker akan menganggur.

## 3. Database

Tambahkan service **MySQL** pada canvas proyek, lalu pastikan `DB_URL` pada service App dan Cron menunjuk ke `${{MySQL.MYSQL_URL}}`. Migration dijalankan oleh Pre-Deploy Command di atas.

## Catatan

- **Jangan pakai `migrate:fresh`** di Railway.
- **Config as Code sudah diddeprekasi.** File `railway.json` / `railway.toml` berhenti dibaca **2026-12-01**. Jangan menambahkannya; penggantinya adalah Infrastructure as Code (`.railway/railway.ts`) lewat Railway CLI.
- Butuh ekstensi PHP lain? Tambahkan ke `RAILPACK_PHP_EXTENSIONS`, bukan ke `Procfile`.

---

# 📂 Struktur Project
