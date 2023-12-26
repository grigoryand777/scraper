FROM php:8.1-fpm

WORKDIR /var/www/html/app

RUN apt-get update && apt-get install -y nginx libpng-dev libjpeg-dev \
     libfreetype6-dev zip unzip git systemctl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install redis && docker-php-ext-enable redis

COPY ./api/composer.json ./api/composer.lock ./
COPY . .
COPY ./fpm/www.conf /usr/local/etc/php-fpm.d
COPY ./fpm/php.ini /usr/local/etc/php
COPY ./nginx /etc/nginx/sites-available

#RUN composer dump-autoload --optimize

CMD ["./entrypoint.sh"]
EXPOSE 80