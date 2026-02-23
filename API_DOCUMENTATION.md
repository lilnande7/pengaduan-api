# Sistem Pengaduan Online API

Sistem pengaduan online yang dibangun dengan Laravel untuk backend dan ReactJS untuk frontend, dengan integrasi WhatsApp API untuk notifikasi.

## 🚀 Features

### Modul Pengaduan (FR-01 sampai FR-05)
- [x] User dapat mengisi form pengaduan
- [x] User dapat upload bukti (gambar/dokumen)
- [x] Sistem menyimpan data ke database MySQL
- [x] Sistem menghasilkan nomor tiket otomatis (format: TCK-YYYYMMDD-0001)
- [x] Sistem mengirim notifikasi WhatsApp ke admin

### Modul Admin (FR-06 sampai FR-12)
- [x] Admin dapat login dengan autentikasi
- [x] Admin dapat melihat daftar tiket dengan filter dan pencarian
- [x] Admin dapat mengubah status tiket (baru, diproses, selesai)
- [x] Admin dapat membalas pengaduan
- [x] Admin dapat menghapus tiket
- [x] Admin dapat melihat statistik dashboard
- [x] Admin dapat export laporan CSV

### Modul Notifikasi (FR-13 sampai FR-15)
- [x] Sistem mengirim pesan WhatsApp saat ada pengaduan baru
- [x] Pesan berisi detail pengaduan lengkap
- [x] Sistem menangani kegagalan pengiriman dengan logging

## 📋 Requirements

- PHP 8.2+
- Composer
- MySQL 5.7+
- Laravel 11
- Fonnte WhatsApp API Token

## 🛠 Installation

1. **Clone repository**
```bash
git clone <repository-url>
cd pengaduan-api
```

2. **Install dependencies**
```bash
composer install
```

3. **Setup environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database di .env**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pengaduan_db
DB_USERNAME=root
DB_PASSWORD=
```

5. **Configure WhatsApp API di .env**
```env
FONNTE_TOKEN=your_fonnte_token_here
ADMIN_PHONE=628123456789
```

6. **Run migrations dan seeders**
```bash
php artisan migrate
php artisan db:seed
```

7. **Create storage link**
```bash
php artisan storage:link
```

8. **Start server**
```bash
php artisan serve
```

## 🔗 API Documentation

Base URL: `http://localhost:8000/api`

### Authentication

#### Register User
```
POST /api/register
Content-Type: application/json

{
    "name": "User Name",
    "email": "user@email.com", 
    "password": "password123",
    "password_confirmation": "password123",
    "role": "user" // optional: admin|user
}
```

#### Login
```
POST /api/login
Content-Type: application/json

{
    "email": "user@email.com",
    "password": "password123"
}

Response:
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {...},
        "access_token": "token_here",
        "token_type": "Bearer"
    }
}
```

## 🗄 Database Schema

### users
- id (PK)
- name
- email (unique)
- password (hashed)
- role (enum: admin, user)
- timestamps

### tickets
- id (PK)
- ticket_number (unique, format: TCK-YYYYMMDD-0001)
- name
- phone  
- email (nullable)
- category
- message (text)
- evidence_file (nullable)
- status (enum: baru, diproses, selesai)
- timestamps

### replies
- id (PK)
- ticket_id (FK to tickets)
- admin_id (FK to users)
- message (text)
- timestamps

## 🔐 Default Users

Setelah running `php artisan db:seed`:

**Admin:**
- Email: admin@pengaduan.com
- Password: admin123

**Demo User:**
- Email: user@demo.com  
- Password: user123

## 📱 WhatsApp Integration

Sistem menggunakan Fonnte API untuk mengirim notifikasi WhatsApp:

1. **Pengaduan Baru** → Notifikasi ke admin
2. **Balasan Admin** → Notifikasi ke user

Format pesan otomatis dengan emoji dan informasi lengkap.

## 🛡 Security Features

- ✅ Password hashing (bcrypt)
- ✅ API Authentication (Laravel Sanctum)
- ✅ Input validation
- ✅ CSRF protection  
- ✅ File upload validation
- ✅ Role-based authorization

## 📊 Performance & Reliability

- ✅ Database indexing
- ✅ Pagination
- ✅ Error logging
- ✅ Exception handling
- ✅ File storage optimization

## 📄 License

MIT License