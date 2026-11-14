#!/bin/sh
set -e

# Pastikan file SQLite tersedia
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chmod -R 775 /var/www/html/database

# Jalankan migrasi & seeder jika tabel users belum ada
php artisan migrate --force
if [ "$(php artisan tinker --execute='echo \DB::table("users")->count();')" = "0" ]; then
    php artisan db:seed --force
fi

# Optimasi konfigurasi & route untuk produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan server pada port dari Render (default 8000)
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}