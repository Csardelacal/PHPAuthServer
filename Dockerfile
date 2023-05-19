FROM php:8-apache AS deploy

#TODO Focus this dockerfile on deploying the applicatio to docker hub

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
COPY dev/docker-php-entrypoint dev/wait-for /usr/local/bin/

# Copy the entire application into the container
COPY composer.* console .htaccess index.php mix-manifest.json ./
COPY assets assets
COPY bin bin
COPY spitfire spitfire

# Use composer to build all the dependencies into the container. This way it
# can be used standalone and will behave consistently.
RUN composer install --no-interaction --no-autoloader --no-dev
RUN composer dump-autoload --no-interaction --optimize

# Enable the session directory being written to.
# In future revisions I would like to revert back to using the default directory, since
# it makes no sense to have this here.
RUN mkdir -p bin/usr/sessions
RUN mkdir -p bin/usr/uploads
RUN chown -R www-data: bin/usr

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

RUN composer install --no-interaction --no-autoloader
RUN composer dump-autoload --no-interaction
