FROM php:apache
RUN docker-php-ext-install mysqli
RUN a2enmod headers
COPY ./my-apache-config.conf /etc/apache2/sites-available/000-default.conf
COPY ./php.ini /var/www/html/php/php.ini
