FROM php:8.3-apache
RUN a2dismod mpm_event mpm_worker || true; a2enmod mpm_prefork; docker-php-ext-install pdo_mysql
COPY . /var/www/html/
EXPOSE 80
