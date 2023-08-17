# ============================
# base image and working directory
# ============================

FROM php:8.2-fpm-alpine
WORKDIR /var/www


# ============================
# linux packages and libraries
# ============================

# update packages
RUN apk update

# install packages
RUN apk add nginx
RUN apk add nano
RUN apk add nano-syntax
RUN apk add sqlite-dev
RUN apk add libzip-dev zip
RUN apk add libxml2-dev
RUN apk add linux-headers
RUN apk add nodejs npm
RUN apk add supervisor
RUN apk add htop

# clear cache
RUN rm -rf /var/cache/apk/*


# ================
# install composer
# ================
RUN curl -sS https://getcomposer.org/installer | php -- \
     --install-dir=/usr/local/bin --filename=composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# ======================
# install php extensions
# ======================
RUN docker-php-ext-install pdo pdo_sqlite sockets zip opcache


# =============================================
# add code and change ownership and permissions
# =============================================

COPY --chown=www-data:www-data --chmod=700 . /var/www


# ==========================
# configure nginx, php, nano
# ==========================

# configure nginx
RUN rm /etc/nginx/http.d/*
RUN cp -R docker/nginx/conf.d/* /etc/nginx/http.d
RUN chmod -R 755 /etc/nginx/http.d

# change permission in /var/lib/nginx, /var/log/nginx and /run/nginx
RUN chown -R www-data:www-data /var/lib/nginx
RUN chown -R www-data:www-data /var/log/nginx
RUN chown -R www-data:www-data /run/nginx

# configure php
COPY docker/php/optigov-adjustments.ini /usr/local/etc/php/conf.d/optigov-adjustments.ini

# configure nano
RUN echo "include /usr/share/nano/*.nanorc" >> /etc/nanorc


# ========================================================
# install composer dependencies and build vue applications
# ========================================================

# create vendor directory for www-data user
RUN mkdir vendor && chown www-data:www-data vendor

# install composer dependencies
RUN composer install --optimize-autoloader --no-dev

# build vue applications
RUN npm install && npm run build


# ======================
# uninstall node and npm
# ======================

# uninstall NodeJS and NPM
RUN apk del nodejs npm


### =============================
### deploy the docker applocation
### =============================

# switch to user `www-data`
USER www-data

# deploy the application
CMD sh docker/deploy-app.sh
