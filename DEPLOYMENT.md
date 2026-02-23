# 🚀 Deployment Guide - Sistem Pengaduan Online

Panduan lengkap untuk deploy sistem pengaduan online ke production.

## 🏗️ Persiapan Server

### Minimum Requirements
- **Server**: Ubuntu 20.04+ / CentOS 8+
- **PHP**: 8.2+
- **MySQL**: 5.7+ atau MariaDB 10.3+
- **Composer**: Latest version
- **Web Server**: Apache 2.4+ atau Nginx 1.18+
- **SSL Certificate**: Untuk HTTPS
- **Memory**: 1GB RAM minimum
- **Storage**: 10GB minimum

### Extensions PHP Yang Diperlukan
```bash
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath php8.2-intl
```

## 📂 Struktur Directory Production
```
/var/www/pengaduan-api/
├── current/          # Symlink ke release terbaru
├── releases/         # Folder release versions
├── shared/           # File shared antar release
│   ├── .env
│   └── storage/
└── repo/            # Git repository
```

## 🔧 Setup Production Server

### 1. Clone Repository
```bash
cd /var/www/
sudo git clone <repository-url> pengaduan-api
cd pengaduan-api
```

### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Environment Configuration
```bash
cp .env.example .env
nano .env
```

**Production .env settings:**
```env
APP_NAME="Sistem Pengaduan Online"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pengaduan.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pengaduan_production
DB_USERNAME=pengaduan_user
DB_PASSWORD=secure_password

# WhatsApp API
FONNTE_TOKEN=your_production_token
ADMIN_PHONE=628123456789

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=mail_password
MAIL_ENCRYPTION=tls
```

### 4. Generate Keys & Setup
```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/pengaduan-api
sudo chmod -R 755 /var/www/pengaduan-api
sudo chmod -R 775 /var/www/pengaduan-api/storage
sudo chmod -R 775 /var/www/pengaduan-api/bootstrap/cache
```

## 🌐 Web Server Configuration

### Apache Virtual Host
```apache
<VirtualHost *:80>
    ServerName pengaduan.yourdomain.com
    DocumentRoot /var/www/pengaduan-api/public

    <Directory /var/www/pengaduan-api/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/pengaduan_error.log
    CustomLog ${APACHE_LOG_DIR}/pengaduan_access.log combined

    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName pengaduan.yourdomain.com
    DocumentRoot /var/www/pengaduan-api/public

    <Directory /var/www/pengaduan-api/public>
        AllowOverride All
        Require all granted
    </Directory>

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt

    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    ErrorLog ${APACHE_LOG_DIR}/pengaduan_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/pengaduan_ssl_access.log combined
</VirtualHost>
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name pengaduan.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name pengaduan.yourdomain.com;
    root /var/www/pengaduan-api/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";

    # File Upload Size
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## 🗄️ Database Setup

### MySQL Production Database
```sql
CREATE DATABASE pengaduan_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pengaduan_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON pengaduan_production.* TO 'pengaduan_user'@'localhost';
FLUSH PRIVILEGES;
```

### Backup Script
```bash
#!/bin/bash
# File: /home/backup/backup_pengaduan.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/backup/pengaduan"
DB_NAME="pengaduan_production"
DB_USER="pengaduan_user"
DB_PASS="secure_password"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/pengaduan-api/storage

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

### Cron Job untuk Backup
```bash
# Edit crontab
sudo crontab -e

# Backup harian jam 2 pagi
0 2 * * * /home/backup/backup_pengaduan.sh

# Cleanup logs bulanan
0 1 1 * * find /var/www/pengaduan-api/storage/logs -name "*.log" -mtime +30 -delete
```

## 🔒 Security Hardening

### 1. Firewall (UFW)
```bash
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443
sudo ufw deny 3306  # Block direct MySQL access
```

### 2. Fail2ban untuk Laravel
```bash
sudo apt install fail2ban

# Create filter
sudo nano /etc/fail2ban/filter.d/laravel.conf
```

```ini
[Definition]
failregex = ^.*\[<HOST>\] Unauthenticated.*$
            ^.*\[<HOST>\] Invalid credentials.*$
ignoreregex =
```

```bash
# Create jail
sudo nano /etc/fail2ban/jail.d/laravel.conf
```

```ini
[laravel]
enabled = true
port = http,https
filter = laravel
logpath = /var/www/pengaduan-api/storage/logs/laravel.log
maxretry = 5
bantime = 3600
findtime = 600
```

### 3. Rate Limiting
Sudah dikonfigurasi di Laravel menggunakan middleware `throttle`.

## 📊 Monitoring & Logs

### 1. Log Rotation
```bash
sudo nano /etc/logrotate.d/laravel-pengaduan
```

```
/var/www/pengaduan-api/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        sudo service php8.2-fpm reload
    endscript
}
```

### 2. Performance Monitoring
```bash
# Install htop untuk monitoring real-time
sudo apt install htop

# Install monitoring tools
sudo apt install sysstat iotop
```

### 3. Application Monitoring
Tambahkan monitoring di `.env`:
```env
LOG_LEVEL=warning
LOG_DEPRECATIONS_CHANNEL=null
```

## 🚀 Deployment Automation

### Deploy Script
```bash
#!/bin/bash
# File: deploy.sh

REPO_URL="git@github.com:username/pengaduan-api.git"
DEPLOY_PATH="/var/www/pengaduan-api"
CURRENT_PATH="$DEPLOY_PATH/current"
RELEASE_PATH="$DEPLOY_PATH/releases/$(date +%Y%m%d_%H%M%S)"

echo "🚀 Starting deployment..."

# Create release directory
mkdir -p $RELEASE_PATH

# Clone repository
git clone $REPO_URL $RELEASE_PATH

# Navigate to release
cd $RELEASE_PATH

# Install dependencies
composer install --optimize-autoloader --no-dev

# Copy shared files
ln -s $DEPLOY_PATH/shared/.env $RELEASE_PATH/.env
ln -s $DEPLOY_PATH/shared/storage $RELEASE_PATH/storage

# Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Update symlink
ln -sfn $RELEASE_PATH $CURRENT_PATH

# Set permissions
chown -R www-data:www-data $RELEASE_PATH
chmod -R 755 $RELEASE_PATH

# Reload web server
systemctl reload apache2

# Cleanup old releases (keep last 5)
ls -1dt $DEPLOY_PATH/releases/* | tail -n +6 | xargs rm -rf

echo "✅ Deployment completed successfully!"
```

## 🔧 Troubleshooting

### Common Issues

1. **Permission Denied**
```bash
sudo chown -R www-data:www-data /var/www/pengaduan-api
sudo chmod -R 755 /var/www/pengaduan-api
```

2. **Storage Link Broken**
```bash
php artisan storage:link --force
```

3. **Config Cache Issues**
```bash
php artisan config:clear
php artisan cache:clear
```

4. **Database Connection**
- Check MySQL service: `sudo systemctl status mysql`
- Verify credentials in `.env`
- Test connection: `php artisan tinker` → `DB::connection()->getPdo();`

5. **File Upload Issues**
- Check `client_max_body_size` in Nginx
- Check `upload_max_filesize` in PHP
- Verify storage permissions

## 📋 Production Checklist

- [ ] Server requirements met
- [ ] Database created and configured
- [ ] .env file configured for production
- [ ] SSL certificate installed
- [ ] Web server configured
- [ ] Permissions set correctly
- [ ] Backup system configured
- [ ] Monitoring tools installed
- [ ] Security hardening applied
- [ ] Domain DNS pointed correctly
- [ ] Email configuration tested
- [ ] WhatsApp API configured and tested
- [ ] Admin account created
- [ ] Application tested end-to-end

## 📞 Support

Untuk bantuan deployment atau troubleshooting:
- 📧 Email: support@yourdomain.com
- 📚 Documentation: API_DOCUMENTATION.md
- 🐛 Issues: GitHub Issues

---

**⚠️ Important:** Selalu test deployment di staging environment sebelum deploy ke production!