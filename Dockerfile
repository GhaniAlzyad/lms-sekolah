FROM php:8.3-cli-alpine

# Instal dependensi sistem dan ekstensi SQLite
RUN apk add --no-cache \
    sqlite-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_sqlite zip

# Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Salin seluruh kode proyek
COPY . .

# Instal dependensi Laravel (mode produksi)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Siapkan izin folder storage & bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Salin & beri izin eksekusi pada skrip startup
COPY deploy.sh /usr/local/bin/deploy.sh
RUN chmod +x /usr/local/bin/deploy.sh

EXPOSE 8000

CMD ["/usr/local/bin/deploy.sh"]