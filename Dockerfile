###########################################################################################
#
#                         Image Docker pour l'application SyGAL
#
###########################################################################################

FROM debian:bullseye AS distrib

ENV TZ="Europe/Paris"

LABEL maintainer="Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>"

ARG PHP_VERSION

ENV PHP_VERSION=${PHP_VERSION}

ENV HTTP_PROXY=${http_proxy} \
    HTTPS_PROXY=${https_proxy} \
    NO_PROXY=${no_proxy} \
    http_proxy=${http_proxy} \
    https_proxy=${https_proxy} \
    no_proxy=${no_proxy}

RUN apt-get -qq update && \
    apt-get install -y \
        apache2 \
        ca-certificates \
        curl \
        ghostscript \
        ghostscript-x \
        gcc \
        git \
        host \
        imagemagick \
        ldap-utils \
        libaio1 \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libldap2-dev \
        libmcrypt-dev \
        libmemcached-dev \
        libmemcached-tools \
        libssl-dev \
        libxml2-dev \
        make \
        memcached \
        nano \
        netcat-openbsd \
        net-tools \
        postgresql-client \
        qpdf \
        ssh \
        ssl-cert \
        telnet \
        unzip \
        vim \
        wget \
        zlib1g-dev

# Saxon/C transform command (https://www.saxonica.com/download/c.xml)
ENV SAXONC_INSTALL_DIR="/opt/Saxonica/SaxonHEC" \
    SAXONC_TRANSFORM_CMD_DEPLOY_DIR="/usr/bin"
RUN wget https://www.saxonica.com/download/libsaxon-HEC-setup64-v11.4.zip -P /tmp/ && \
    unzip -o /tmp/libsaxon-HEC-setup64-v11.4.zip -d /tmp/ && \
    mkdir -p ${SAXONC_INSTALL_DIR} && cp -r /tmp/libsaxon-HEC-11.4/* ${SAXONC_INSTALL_DIR}/ && \
    cd ${SAXONC_INSTALL_DIR} && \
    cp *.so /usr/lib/. && cp -r rt /usr/lib/. && cp -r saxon-data /usr/lib/. && \
    export SAXONC_HOME=/usr/lib && \
    cd command && ./buildhec-command.sh && ln -s ${SAXONC_INSTALL_DIR}/command/transform ${SAXONC_TRANSFORM_CMD_DEPLOY_DIR} && \
    cd

## Liquibase (+ Java)
#ADD resources/jre-8u321-linux-x64.tar.gz /opt/java/
#ENV JAVA_HOME="/opt/java/jre1.8.0_321"
#ADD resources/liquibase-4.8.0.tar.gz /opt/liquibase/
#ENV PATH="/opt/liquibase/:${PATH}"


###########################################################################################

FROM composer:2.5.5 AS get-composer

FROM distrib AS php

ENV PHP_CONF_LOCAL_DIR=docker/configs/php \
    PHP_CONF_DIR=/etc/php/${PHP_VERSION} \
    FPM_PHP_LOG_FILE=/var/log/php-fpm.log

# Repositories fournissant PHP 5.x, 7.x et 8.x
RUN apt-get -qq update && \
    apt-get -y install apt-transport-https lsb-release ca-certificates curl && \
    curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg && \
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
#    wget --no-check-certificate -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
#    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list

RUN apt-get -qq update && \
    apt-get install -y \
        php-pear \
        php${PHP_VERSION} \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-dev \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-gettext \
        php${PHP_VERSION}-iconv \
        php${PHP_VERSION}-imagick \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-ldap \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-memcached \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-opcache \
        php${PHP_VERSION}-pgsql \
        php${PHP_VERSION}-soap \
#        php${PHP_VERSION}-xdebug \ --> cf. install à part ci-après
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-common \
        php${PHP_VERSION}-opcache \
        php${PHP_VERSION}-readline

# Forçage de la version de PHP CLI
RUN update-alternatives --set php /usr/bin/php${PHP_VERSION}

# Installation manuelle de xdebug 3.2.2, car les 3.3.0/1/2 provoquent une "Segmentation fault" au 22/05/2024 (à cause de PHP 8.0 ?)
RUN pecl install xdebug-3.2.2 && \
    echo "zend_extension=xdebug" > ${PHP_CONF_DIR}/fpm/conf.d/20-xdebug.ini && \
    echo "zend_extension=xdebug" > ${PHP_CONF_DIR}/cli/conf.d/20-xdebug.ini

# Composer
COPY --from=get-composer /usr/bin/composer /usr/local/bin/composer

# Configuration PHP, php-fpm.
ADD ${PHP_CONF_LOCAL_DIR}/fpm/pool.d/www.conf.part /tmp/
RUN cat /tmp/www.conf.part >> ${PHP_CONF_DIR}/fpm/pool.d/www.conf && rm /tmp/www.conf.part
ADD ${PHP_CONF_LOCAL_DIR}/fpm/conf.d/99-sygal.ini ${PHP_CONF_DIR}/fpm/conf.d/
ADD ${PHP_CONF_LOCAL_DIR}/cli/conf.d/99-sygal.ini ${PHP_CONF_DIR}/cli/conf.d/

# Création du fichier pour les logs FPM (cf. fpm/pool.d/www.conf.part)
RUN touch ${FPM_PHP_LOG_FILE} && \
    chown www-data:www-data ${FPM_PHP_LOG_FILE}


###########################################################################################


FROM php AS apache

ENV APACHE_CONF_LOCAL_DIR=docker/configs/apache \
    APACHE_CONF_DIR=/etc/apache2

RUN a2enmod actions alias rewrite ssl proxy proxy_fcgi setenvif headers && \
    a2dismod mpm_event && a2enmod mpm_worker
ADD ${APACHE_CONF_LOCAL_DIR}/conf-available/security.conf ${APACHE_CONF_DIR}/conf-available/security-unicaen.conf
ADD ${APACHE_CONF_LOCAL_DIR}/conf-available/livelog.conf  ${APACHE_CONF_DIR}/conf-available/livelog.conf
ADD ${APACHE_CONF_LOCAL_DIR}/conf-available/api.conf      ${APACHE_CONF_DIR}/conf-available/api.conf

RUN a2disconf security.conf && \
    a2enconf security-unicaen.conf \
             php${PHP_VERSION}-fpm \
             livelog.conf \
             api.conf

# Symlink apache access and error logs to stdout/stderr so Docker logs shows them.
RUN ln -sf /dev/stdout /var/log/apache2/access.log
RUN ln -sf /dev/stdout /var/log/apache2/other_vhosts_access.log
RUN ln -sf /dev/stderr /var/log/apache2/error.log

# Configuration Apache.
ADD ${APACHE_CONF_LOCAL_DIR}/ports.conf     ${APACHE_CONF_DIR}/ports.conf
ADD ${APACHE_CONF_LOCAL_DIR}/sygal.conf     ${APACHE_CONF_DIR}/sites-available/sygal.conf
ADD ${APACHE_CONF_LOCAL_DIR}/sygal-ssl.conf ${APACHE_CONF_DIR}/sites-available/sygal-ssl.conf
RUN a2ensite sygal sygal-ssl


###########################################################################################


FROM apache AS bootstrap

# Nettoyage
RUN apt-get autoremove -y && apt-get clean && rm -rf /tmp/* /var/tmp/*

# Copie les fichiers situés dans ./docker/entrypoint.d dans le dossier /entrypoint.d de l'image.
# Les scripts exécutables parmi eux seront exécutés au démarrage du container (cf. entrypoint.sh).
# Attention : les noms de fichiers ne doivent être constitués que de lettres minuscules ou majuscules,
# de chiffres, de tirets bas (underscore) ou de tirets ; extension interdite, donc.
#ADD docker/entrypoint.d/* /entrypoint.d/
## Copie des scripts complémentaires à lancer au démarrage du container.
COPY docker/entrypoint.d/* /entrypoint.d/

# Entry point
ADD docker/entrypoint.sh /sbin/entrypoint.sh
RUN chmod 755 /sbin/entrypoint.sh
CMD ["/sbin/entrypoint.sh"]

WORKDIR /app

## Dépendances PHP puis sources puis autoloading puis scripts (favorise la mise en cache Docker)
#COPY composer.json ./
#COPY composer.lock ./
#RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
#COPY . /app
#RUN composer dump-autoload --optimize
#RUN composer run-script post-install-cmd

# Répertoire pour l'upload de fichiers
RUN mkdir -p upload && \
  chown -R www-data:root upload && \
  chmod -R 770 upload

# Cache Laminas
RUN mkdir -p data/cache && chmod 777 data/cache
RUN rm -rf data/cache/*

# Cache Doctrine
RUN mkdir -p data/DoctrineModule/cache && chmod 777 data/DoctrineModule/cache #&& rm -rf data/DoctrineModule/cache/*
RUN mkdir -p data/DoctrineORMModule/Proxy && chmod 777 data/DoctrineORMModule/Proxy && rm -rf data/DoctrineORMModule/Proxy/*

#RUN vendor/bin/laminas-development-mode enable  # nécessaire !
