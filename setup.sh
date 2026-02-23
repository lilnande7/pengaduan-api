#!/bin/bash

# Sistem Pengaduan Online - Setup Script
# Digunakan untuk setup awal sistem pengaduan

echo "🚀 Setting up Sistem Pengaduan Online..."
echo "========================================"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: File artisan tidak ditemukan. Pastikan Anda berada di root directory Laravel."
    exit 1
fi

echo "📦 Installing Composer dependencies..."
composer install

echo "🔑 Generating application key..."
php artisan key:generate

echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "🌱 Seeding database with default data..."
php artisan db:seed --force

echo "🔗 Creating storage link..."
php artisan storage:link

echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear  
php artisan route:clear
php artisan view:clear

echo "✅ Setup completed successfully!"
echo ""
echo "🔐 Default Admin Account:"
echo "   Email: admin@pengaduan.com"
echo "   Password: admin123"
echo ""
echo "👤 Demo User Account:"  
echo "   Email: user@demo.com"
echo "   Password: user123"
echo ""
echo "📋 Next Steps:"
echo "1. Configure your .env file with database and WhatsApp API settings"
echo "2. Run: php artisan serve"
echo "3. Visit: http://localhost:8000/test.html for testing"
echo "4. API Base URL: http://localhost:8000/api"
echo ""
echo "📚 Documentation available in API_DOCUMENTATION.md"
echo ""
echo "🎉 Happy coding!"