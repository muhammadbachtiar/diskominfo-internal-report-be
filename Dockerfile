# Use specific version of FrankenPHP
FROM dunglas/frankenphp:1.1-php8.3

# Set domain for Caddy
ENV SERVER_NAME="localhost"

# Install PHP extensions dan tools
RUN apt-get update && apt-get install -y \
    ca-certificates curl unzip git gnupg2 ghostscript \
    && install-php-extensions \
        bcmath \
        pdo_pgsql \
        pdo_mysql \
        xml \
        mbstring \
        zip \
        curl \
        pcntl \
        gd \
        exif \
        imagick \
    && rm -rf /var/lib/apt/lists/*

# Install Composer directly
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    chmod +x /usr/bin/composer
RUN chmod +x /usr/bin/composer && \
    composer --version

# Install Node.js v18
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && node -v && npm -v

# Salin file Laravel
WORKDIR /app
COPY . .

# hilangin htaccess,info.php
RUN rm -f .htaccess && rm -f info.php
# Install dependencies dan build asset (jika pakai Vite)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs
#RUN npm install && npm run build
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan key:generate
RUN php artisan optimize:clear
# RUN php artisan migrate --force
# RUN  php artisan passport:install



  # Aktifkan PHP production config
COPY ./php.ini $PHP_INI_DIR/php.ini

# Jalankan FrankenPHP dengan Octane (worker mode)
EXPOSE 8000
CMD ["php","artisan","octane:frankenphp","--host=0.0.0.0","--port=8000", "--workers=8","--max-requests=1000"]