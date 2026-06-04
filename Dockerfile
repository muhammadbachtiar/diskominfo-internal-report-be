# Use specific version of FrankenPHP
FROM dunglas/frankenphp:1.2-php8.3

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

# Fix ImageMagick security policy - allow PDF reading (blocked by default on Debian/Ubuntu)
# Without this, Spatie\PdfToImage and Imagick will silently fail when converting PDF to image
RUN find /etc/ImageMagick* -name "policy.xml" -exec sed -i \
    's|<policy domain="coder" rights="none" pattern="PDF" />|<policy domain="coder" rights="read\|write" pattern="PDF" />|g' {} \;

# Install Composer directly
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    chmod +x /usr/bin/composer && \
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
# Set directory permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache



# Aktifkan PHP production config
COPY ./php.ini $PHP_INI_DIR/php.ini

# Jalankan FrankenPHP dengan Octane (worker mode)
EXPOSE 8000
CMD ["php","artisan","octane:frankenphp","--host=0.0.0.0","--port=8000", "--workers=8","--max-requests=1000"]