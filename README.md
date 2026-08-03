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
user_id bigint [not null]
ktp_number varchar [unique]
emergency_contact_name varchar
emergency_contact_phone varchar
job varchar
created_at timestamp
updated_at timestamp
}

Table rooms {
id bigint [pk]
room_number varchar [unique]
floor int
price_per_month decimal(12,2)
status varchar
is_active boolean
description text
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
tenant_id bigint
room_id bigint
start_date date
end_date date
monthly_price decimal(12,2)
deposit_amount decimal(12,2)
status varchar
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

payment_method varchar
status varchar

proof_image varchar

verified_by bigint

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
description text

amount decimal(12,2)

expense_date date

created_by bigint

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

Ref: expenses.created_by > users.id

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

    - **Caretaker (Penjaga Kos)**
        - Mengelola kondisi kamar
        - Menangani laporan maintenance
        - Membantu operasional kos

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

# 📂 Struktur Project
