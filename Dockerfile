FROM php:8.3.15-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    nodejs \
    npm \
    git \
    curl \
    gnupg \
    cron \
    supervisor \
    nano \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Копируем дефолтный php.ini
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

# Настраиваем upload limits
RUN sed -i 's/^upload_max_filesize.*/upload_max_filesize = 100M/' /usr/local/etc/php/php.ini \
 && sed -i 's/^post_max_size.*/post_max_size = 100M/' /usr/local/etc/php/php.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
