########################################################################################################################
#                           Image pour les tests unitaires/fonctionnels
#                            NE partant PAS de l'image Unicaen de base.
########################################################################################################################

FROM thomasbisignani/docker-apache-php-oracle

LABEL maintainer="Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>"

# Mise à niveau de la distrib.
RUN apt-get update && apt-get install -y \
    git \
    make \
    netcat-openbsd \
    php-soap \
    php5-curl \
    php5-gd \
    php5-intl \
    php5-ldap \
    php5-xdebug \
    phpunit \
    subversion \
    vim \
    wget \
 && rm -rf /var/lib/apt/lists/*

#RUN pear channel-update pear.php.net && \
#    pecl channel-update pecl.php.net

# Installation de Composer dans /usr/local/bin.
RUN cd $HOME && \
    curl -sS https://getcomposer.org/installer | php && \
    chmod +x composer.phar && \
    mv composer.phar /usr/local/bin/composer

# Ajustement des config PHP.
RUN echo "date.timezone = Europe/Paris"       >> /etc/php5/apache2/conf.d/01-timezone.ini && \
    echo "upload_max_filesize = 51M"          >> /etc/php5/apache2/conf.d/02-uploads.ini && \
    echo "post_max_size = 60M"                >> /etc/php5/apache2/conf.d/02-uploads.ini && \
    echo "error_reporting = E_ALL"            >> /etc/php5/apache2/conf.d/01-errors.ini && \
    echo "display_startup_errors = 1"         >> /etc/php5/apache2/conf.d/01-errors.ini && \
    echo "display_errors = 1"                 >> /etc/php5/apache2/conf.d/01-errors.ini && \
    echo "error_reporting = E_ALL"            >> /etc/php5/cli/conf.d/01-errors.ini && \
    echo "display_startup_errors = 1"         >> /etc/php5/cli/conf.d/01-errors.ini && \
    echo "display_errors = 1"                 >> /etc/php5/cli/conf.d/01-errors.ini && \
    echo "xdebug.remote_enable = 1"           >> /etc/php5/apache2/conf.d/20-xdebug.ini && \
    echo "xdebug.remote_connect_back = 1"     >> /etc/php5/apache2/conf.d/20-xdebug.ini && \
    echo "xdebug.profiler_enable_trigger = 1" >> /etc/php5/apache2/conf.d/20-xdebug.ini && \
    echo "xdebug.max_nesting_level = 250"     >> /etc/php5/apache2/conf.d/20-xdebug.ini

# Activation de l'extension PHP OCI8 en mode CLI (non fait par "thomasbisignani/docker-apache-php-oracle").
RUN echo "extension=oci8.so" > /etc/php5/cli/conf.d/30-oci8.ini

# Spécification du répertoire courant dans l'image.
WORKDIR /webapp

# Installation des dépendances Composer *en permettant la mise en cache Docker*.
# L'astuce consiste à faire l'installation avant la copie des sources de l'appli car cette dernière invalide le cache.
# (NB: --no-scripts --no-autoloader).
# cf. https://www.sentinelstand.com/article/composer-install-in-dockerfile-without-breaking-cache
COPY composer.json composer.lock auth.json ./
#RUN composer install --no-interaction --no-suggest --no-scripts --no-autoloader && rm -f auth.json

# Copie des sources de l'appli dans l'image.
COPY . ./

# Reste du travail Composer qui ne peut se faire sans les sources de l'appli.
RUN composer dump-autoload --optimize

# Création du répertoire des proxies Doctrine.
RUN mkdir -p data/DoctrineORMModule/Proxy && chmod 777 data/DoctrineORMModule/Proxy

# Création de la config et activation du site Apache.
COPY apache.conf /etc/apache2/sites-available/sodoct.conf
RUN a2ensite sodoct
