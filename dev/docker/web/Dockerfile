FROM php:8-apache AS deploy

# PHP_CPPFLAGS are used by the docker-php-ext-* scripts, which in turn are required
# in order to build the intl extension.
ENV PHP_CPPFLAGS="$PHP_CPPFLAGS"

RUN apt-get update -y \
    && apt-get upgrade -y  \
    && apt-get install memcached libicu-dev git zip unzip libgd3 zlib1g-dev libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libfreetype6-dev -y 
	
RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-configure gd --with-webp --with-jpeg --with-xpm --with-freetype \
    && docker-php-ext-install mysqli pdo pdo_mysql gd \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl 

RUN a2enmod rewrite
RUN service apache2 restart

# Set a custom document root from the Web server
ARG APACHE_DOCUMENT_ROOT="/var/www/phpauth/"
ARG APP_ROOT="/var/www/phpauth/"

WORKDIR ${APP_ROOT}

RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy utilities that the application can use to be more consistent during
# bootstrap.
COPY docker-php-entrypoint wait-for /usr/local/bin/

# Enable the session directory being written to.
# In future revisions I would like to revert back to using the default directory, since
# it makes no sense to have this here.
VOLUME bin/usr/
#RUN chown -R www-data: bin/usr

#TODO: Make the storage and public storage directories writable.

FROM deploy AS debug

# install xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo 'xdebug.mode = debug' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.remote_enable = 1' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.remote_autostart = 1' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.client_port=9000' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.start_with_request = yes' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/php.ini

# Web driver needs libzip dev to work.
RUN apt install netcat -y

# Web driver needs libzip dev to work.
RUN apt install libzip-dev -y
RUN docker-php-ext-install zip

FROM debug as integration

COPY xdebug/001-xdebug.conf /etc/apache2/sites-enabled/
COPY xdebug/ /opt/commishes/xdebug/

RUN sed "s/xdebug.mode = debug/xdebug.mode = coverage/i" -i"" /usr/local/etc/php/php.ini
RUN echo 'opcache.enable=0' >> /usr/local/etc/php/conf.d/xebug-integration.ini
RUN service apache2 restart
RUN echo 'auto_prepend_file=/opt/commishes/xdebug/before.php' >> /usr/local/etc/php/conf.d/xebug-integration.ini
RUN echo 'auto_append_file=/opt/commishes/xdebug/after.php' >> /usr/local/etc/php/conf.d/xebug-integration.ini
RUN mkdir --mode=0777 /var/www/coverage/
