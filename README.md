# 🎫 Sistem Pengaduan Online - API

Sistem Pengaduan Online berbasis Laravel 11 dengan notifikasi WhatsApp realtime menggunakan Fonnte API. Sistem ini menyediakan platform untuk masyarakat melaporkan pengaduan dan admin untuk mengelolanya.

## ✨ Fitur Utama

### 📱 **User Features (Masyarakat)**
- ✅ Pengajuan pengaduan dengan upload bukti
- ✅ Tracking status pengaduan dengan nomor tiket
- ✅ Notifikasi WhatsApp otomatis saat ada balasan
- ✅ Kategori pengaduan (Layanan Publik, Infrastruktur, Keamanan, dll)
- ✅ Sistem nomor tiket unik (TCK-YYYYMMDD-XXXX)

### 👨‍💼 **Admin Features**  
- ✅ Dashboard statistik realtime
- ✅ Management pengaduan (CRUD operations)
- ✅ Update status: Baru → Diproses → Selesai
- ✅ Sistem reply/balasan ke user
- ✅ Export laporan ke CSV
- ✅ Filter dan pencarian pengaduan
- ✅ Notifikasi WhatsApp untuk admin

### 📱 **WhatsApp Integration**
- ✅ Notifikasi realtime ke admin saat ada pengaduan baru
- ✅ Notifikasi ke user saat ada balasan dari admin
- ✅ Laporan harian otomatis ke admin
- ✅ Custom notifications dan testing tools

## 🛠️ Tech Stack

- **Backend:** Laravel 11, PHP 8.1+
- **Database:** SQLite/MySQL (compatible)
- **Authentication:** Laravel Sanctum (JWT)
- **WhatsApp API:** Fonnte
- **Frontend:** HTML, TailwindCSS, Axios
- **File Storage:** Laravel Storage (local/public)

## 🚀 Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- SQLite or MySQL
- Fonnte WhatsApp API token

### 1. Clone Repository
\`\`\`bash
git clone https://github.com/lilnande7/pengaduan-api.git
cd pengaduan-api
\`\`\`

### 2. Install Dependencies
\`\`\`bash
composer install
npm install # if using frontend assets
\`\`\`

### 3. Environment Setup
\`\`\`bash
cp .env.example .env
php artisan key:generate
\`\`\`

### 4. Configure Environment
Edit \`.env\` file:
\`\`\`env
DB_CONNECTION=sqlite
DB_DATABASE=pengaduan_db

# WhatsApp Configuration
FONNTE_TOKEN=your_fonnte_token_here
ADMIN_PHONE=628xxxxxxxxxx
\`\`\`

### 5. Database Setup
\`\`\`bash
# Create SQLite database
touch pengaduan_db

# Run migrations
php artisan migrate

# Seed sample data (optional)
php artisan db:seed
\`\`\`

### 6. Storage Setup
\`\`\`bash
php artisan storage:link
\`\`\`

### 7. Run Server
\`\`\`bash
php artisan serve
\`\`\`

## 📚 API Documentation

### Authentication
- \`POST /api/login\` - Admin login
- \`POST /api/logout\` - Logout

### Public Endpoints
- \`POST /api/tickets\` - Submit new ticket
- \`GET /api/tickets/{ticket}\` - View ticket details
- \`POST /api/tickets/check-status\` - Check ticket status

### Admin Endpoints (Protected)
- \`GET /api/admin/dashboard\` - Dashboard statistics
- \`GET /api/admin/tickets\` - List all tickets
- \`PUT /api/admin/tickets/{id}/status\` - Update ticket status
- \`DELETE /api/admin/tickets/{id}\` - Delete ticket
- \`GET /api/admin/export-tickets\` - Export to CSV

### WhatsApp Endpoints (Admin Only)
- \`POST /api/whatsapp/test-connection\` - Test WhatsApp connection
- \`POST /api/whatsapp/send-daily-summary\` - Send daily summary
- \`POST /api/whatsapp/send-custom-message\` - Send custom message
- \`GET /api/whatsapp/settings\` - View WhatsApp settings

## 🎨 Frontend Demo

Access demo interfaces:
- **Ticket Management:** \`http://localhost:8000/ticket-management.html\`
- **WhatsApp Demo:** \`http://localhost:8000/whatsapp-demo.html\`
- **User Interface:** \`http://localhost:8000/pengaduan.html\`

## 📱 WhatsApp Configuration

1. Register at [Fonnte Console](https://console.fonnte.com/)
2. Get your API token
3. Add token to \`.env\` file:
   \`\`\`env
   FONNTE_TOKEN=your_token_here
   ADMIN_PHONE=628123456789
   \`\`\`

### WhatsApp Features
- **Auto Notifications:** New tickets, status updates, replies
- **Daily Reports:** Automatic daily summary
- **Manual Testing:** Test endpoints for connectivity
- **Custom Messages:** Send custom notifications

## ⚡ Commands

\`\`\`bash
# Send daily summary to admin
php artisan whatsapp:daily-summary

# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
\`\`\`

## 📊 Database Schema

### Tables
- \`users\` - Admin users
- \`tickets\` - Pengaduan/tickets  
- \`replies\` - Balasan/responses
- \`personal_access_tokens\` - API tokens

### Key Relationships
- User hasMany Tickets (admin responses)
- Ticket hasMany Replies
- Reply belongsTo User (admin)
- Reply belongsTo Ticket

## 🔒 Security Features

- ✅ JWT Authentication with Sanctum
- ✅ Role-based authorization (admin middleware)
- ✅ CORS configuration
- ✅ Input validation & sanitization
- ✅ File upload restrictions
- ✅ Rate limiting

## 📈 Monitoring & Logs

- Application logs: \`storage/logs/laravel.log\`
- WhatsApp logs: Check via \`/api/whatsapp/logs\`
- Performance monitoring via Laravel Telescope (optional)

## 👨‍💻 Developer

**Fer Nanda**
- GitHub: [@lilnande7](https://github.com/lilnande7)
- Email: bijionta221@gmail.com

## 📋 Features Checklist

### ✅ Completed Features
- [x] User ticket submission with file upload
- [x] Admin authentication & authorization  
- [x] Ticket management (CRUD)
- [x] Status workflow management
- [x] Reply system
- [x] WhatsApp notifications (Fonnte)
- [x] Dashboard with statistics
- [x] CSV export functionality
- [x] Responsive web interfaces
- [x] API documentation
- [x] Database seeding
- [x] Error handling & logging

### 🎯 System Requirements Met
- [x] **FR-01:** Pengajuan pengaduan online
- [x] **FR-02:** Upload bukti pendukung  
- [x] **FR-03:** Tracking status pengaduan
- [x] **FR-04:** Notifikasi WhatsApp otomatis
- [x] **FR-05:** Kategori pengaduan
- [x] **FR-06:** Login admin
- [x] **FR-07:** Dashboard admin
- [x] **FR-08:** Management pengaduan
- [x] **FR-09:** Update status pengaduan
- [x] **FR-10:** Sistem reply
- [x] **FR-11:** Laporan dan ekspor
- [x] **FR-12:** Filter dan pencarian
- [x] **FR-13:** Notifikasi realtime
- [x] **FR-14:** Template notifikasi
- [x] **FR-15:** Integrasi WhatsApp API

**✨ Status: Not Ready Yet**
