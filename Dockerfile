FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    curl \
    && apt-get clean

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/html
COPY . /var/www/html

RUN composer install

CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
