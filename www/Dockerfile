FROM php:8.2-apache

# Включаем mod_rewrite (для чистых URL)
RUN a2enmod rewrite

# Копируем файлы демо
COPY . /var/www/html/

# Apache — разрешаем .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80
