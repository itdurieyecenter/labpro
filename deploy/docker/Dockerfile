FROM php:8.0-apache

# Ekstensi yang dibutuhkan CodeIgniter 3 + driver mysqli
RUN docker-php-ext-install -j"$(nproc)" mysqli \
    && a2enmod rewrite headers expires

COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY zz-lispro.ini /usr/local/etc/php/conf.d/zz-lispro.ini

# Sembunyikan versi server
RUN printf 'ServerTokens Prod\nServerSignature Off\nServerName lispro\n' > /etc/apache2/conf-available/zz-hardening.conf \
    && a2enconf zz-hardening

# Folder session PHP: image resmi php:8.0-apache tidak menyetel session.save_path
RUN mkdir -p /var/lib/php/sessions \
    && chown www-data:www-data /var/lib/php/sessions \
    && chmod 700 /var/lib/php/sessions

WORKDIR /var/www/html
