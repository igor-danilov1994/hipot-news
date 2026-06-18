FROM php:8.2-apache

RUN a2enmod rewrite

# Копируем файлы демо-стенда
COPY www/ /var/www/html/

# Разрешаем .htaccess
RUN echo '<Directory /var/www/html>\n    AllowOverride All\n    Options -Indexes\n</Directory>' \
    >> /etc/apache2/apache2.conf

EXPOSE 80
