FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www

COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

RUN composer install --no-scripts --no-autoloader
RUN npm ci

COPY . .

RUN composer dump-autoload --optimize
RUN npm run build

RUN touch database/database.sqlite
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000