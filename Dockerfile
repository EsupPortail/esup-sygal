###########################################################################################
#
#                               Image pour le dev.
#
#         Montage des sources attendu dans le volume "/webapp" du container.
#
###########################################################################################

FROM php:7-apache

LABEL maintainer="Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>"

# Mise à niveau de la distrib et installation des packages requis.
RUN apt-get update && apt-get install -y \
        ghostscript \
        git \
        libaio1 \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libldap2-dev \
        libmcrypt-dev \
        libmemcached-dev \
        libssl-dev \
        libxml2-dev \
        make \
        netcat-openbsd \
        ssl-cert \
        subversion \
        unzip \
        vim \
        wget \
        zlib1g-dev
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu && \
    docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install -j$(nproc) \
        curl \
        gd \
        gettext \
        iconv \
        intl \
        ldap \
        mbstring \
        opcache \
        soap \
        zip
RUN rm -rf /var/lib/apt/lists/*

# Installation d'extensions PECL
RUN pear config-set http_proxy "$HTTP_PROXY" && \
    pecl install xdebug && docker-php-ext-enable xdebug && \
    pecl install memcached && docker-php-ext-enable memcached

# Package PHP Oracle OCI8
ADD docker/instantclient-basiclite-linux.x64-12.2.0.1.0.zip /tmp/
ADD docker/instantclient-sdk-linux.x64-12.2.0.1.0.zip /tmp/
ADD docker/instantclient-sqlplus-linux.x64-12.2.0.1.0.zip /tmp/
RUN unzip /tmp/instantclient-basiclite-linux.x64-12.2.0.1.0.zip -d /usr/local/ && \
    unzip /tmp/instantclient-sdk-linux.x64-12.2.0.1.0.zip -d /usr/local/ && \
    unzip /tmp/instantclient-sqlplus-linux.x64-12.2.0.1.0.zip -d /usr/local/ && \
    ln -s /usr/local/instantclient_12_2 /usr/local/instantclient && \
    ln -s /usr/local/instantclient/libclntsh.so.12.1 /usr/local/instantclient/libclntsh.so && \
    ln -s /usr/local/instantclient/sqlplus /usr/bin/sqlplus && \
    echo 'export LD_LIBRARY_PATH="/usr/local/instantclient"' >> /etc/apache2/envvars && \
#    echo 'export LD_LIBRARY_PATH="/usr/local/instantclient"' >> /root/.bashrc && \
#    echo 'umask 002' >> /root/.bashrc && \
    echo 'instantclient,/usr/local/instantclient' | pecl install oci8 && \
    echo "extension=oci8.so" > /usr/local/etc/php/conf.d/php-oci8.ini

ENV LD_LIBRARY_PATH /usr/local/instantclient

# Config PHP.
ADD docker/php.conf /usr/local/etc/php/conf.d/webapp.ini
#ADD docker/php-opcache.ini /usr/local/etc/php/conf.d/01-opcache.ini

# Configuration et activation des sites Apache
ADD docker/apache-ports.conf    /etc/apache2/ports.conf
ADD docker/apache-site.conf     /etc/apache2/sites-available/webapp.conf
ADD docker/apache-site-ssl.conf /etc/apache2/sites-available/webapp-ssl.conf
RUN a2enmod headers alias rewrite ssl && \
    a2ensite webapp && \
    a2ensite webapp-ssl

# Install Composer.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
