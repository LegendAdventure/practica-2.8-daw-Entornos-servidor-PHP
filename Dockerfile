FROM php:8.3-apache

# Instalar extensiones de MySQL para PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apuntar el DocumentRoot de Apache a la carpeta public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitar mod_rewrite (necesario para las rutas limpias /posts/show/1)
RUN a2enmod rewrite

# Permitir que Apache lea los archivos .htaccess
# (por defecto AllowOverride está en "None" y el .htaccess se ignora)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf