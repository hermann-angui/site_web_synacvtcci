FROM php:8.1-fpm
MAINTAINER Angui hermann <ha@link2b.fr>

RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    zip \
    rsync \
    gzip \
    nano \
    sudo \
    cron \
    supervisor \
    ntp \
    nodejs \
    npm \
    yarn \
    git \
    zip \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libicu-dev \
    libpq-dev \
    libxpm-dev \
    libvpx-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    libxrender1 \
    libfontconfig1 \
    fontconfig \
    libfontconfig1-dev \
    acl

RUN apt-get update && \
    apt-get install -y --no-install-recommends libssl-dev zlib1g-dev curl git unzip netcat libxml2-dev libpq-dev libzip-dev && \
    pecl install apcu && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) zip opcache intl pdo_pgsql pgsql pdo_mysql bcmath exif gd gettext xsl && \
    docker-php-ext-enable apcu pdo_pgsql pdo_mysql sodium && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN docker-php-ext-configure gd --with-jpeg --with-freetype

RUN curl -sS https://getcomposer.org/installer | php -- \
   --install-dir=/usr/bin --filename=composer --version=2.1.9

COPY ./php-fpm/php.ini $PHP_INI_DIR/
COPY ./php-fpm/xdebug.ini $PHP_INI_DIR/conf.d/

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

WORKDIR /var/www/html

# Use supervisor to start cron, cron-tab and php-fpm
COPY ./php-fpm/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
CMD ["/usr/bin/supervisord"]

RUN echo 'deb [trusted=yes] https://repo.symfony.com/apt/ /' | sudo tee /etc/apt/sources.list.d/symfony-cli.list
RUN sudo apt update
RUN sudo apt install symfony-cli

CMD php-fpm
