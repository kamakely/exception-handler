FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    curl \
    && apt-get clean

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip

WORKDIR /var/www/html
COPY . /var/www/html

RUN composer install

CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
