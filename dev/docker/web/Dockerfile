FROM php:7-apache as staging

# PHP_CPPFLAGS are used by the docker-php-ext-* scripts, which in turn are required
# in order to build the intl extension.
ENV PHP_CPPFLAGS="$PHP_CPPFLAGS -std=c++11"

RUN apt-get update -y \
    && apt-get upgrade -y  \
    && apt-get install memcached libicu-dev git zip unzip libgd3 libzip-dev libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libfreetype6-dev -y 
	
RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-configure gd --with-webp --with-jpeg --with-xpm --with-freetype \
	&& docker-php-ext-install zip \
    && docker-php-ext-install mysqli pdo pdo_mysql gd \
    && docker-php-ext-install opcache \
    && docker-php-ext-install bcmath \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl 
 
# install xdebug
RUN pecl install xdebug-3.1.6 && docker-php-ext-enable xdebug
RUN echo 'xdebug.mode = debug' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.client_port=9000' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/php.ini

# Assertions must stop the code so we can check what happened
RUN echo 'assert.exception=1' >> /usr/local/etc/php/php.ini

RUN a2enmod rewrite
RUN service apache2 restart

WORKDIR /var/www/html

# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# COPY composer.* ./
# RUN composer install --no-interaction --no-autoloader --no-dev
# RUN composer dump-autoload --no-interaction --optimize

# Create a new stage as integration from the staging for the sake of integration testing
# This requires our application to collect coverage information.
FROM staging as integration
RUN apt-get install jq -y
RUN sed "s/xdebug.mode = debug/xdebug.mode = coverage/i" -i"" /usr/local/etc/php/php.ini
RUN echo 'opcache.enable=0' >> /usr/local/etc/php/php.ini
COPY xdebug/001-xdebug.conf /etc/apache2/sites-enabled/
COPY xdebug/ /opt/commishes/xdebug/
RUN service apache2 restart
RUN echo 'auto_prepend_file=/opt/commishes/xdebug/before.php' >> /usr/local/etc/php/php.ini
RUN echo 'auto_append_file=/opt/commishes/xdebug/after.php' >> /usr/local/etc/php/php.ini
RUN mkdir --mode=0777 /var/www/coverage/
