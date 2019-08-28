###########################################################################################
#
#                               Image pour le dev.
#
###########################################################################################

ARG PHP_VERSION

FROM unicaen-dev-php${PHP_VERSION}-apache

LABEL maintainer="Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>"

## Installation de packages requis.
RUN apt-get update -qq && \
    apt-get install -y \
        ghostscript-x \
        php${PHP_VERSION}-imagick

# Nettoyage
RUN apt-get autoremove -y && apt-get clean && rm -rf /tmp/* /var/tmp/*

# Symlink apache access and error logs to stdout/stderr so Docker logs shows them
RUN ln -sf /dev/stdout /var/log/apache2/access.log
RUN ln -sf /dev/stdout /var/log/apache2/other_vhosts_access.log
RUN ln -sf /dev/stderr /var/log/apache2/error.log

# Configuration Apache, PHP et FPM
ADD docker/apache-ports.conf     ${APACHE_CONF_DIR}/ports.conf
ADD docker/apache-site.conf      ${APACHE_CONF_DIR}/sites-available/app.conf
ADD docker/apache-site-ssl.conf  ${APACHE_CONF_DIR}/sites-available/app-ssl.conf
ADD docker/php/fpm/pool.d/www.conf   ${PHP_CONF_DIR}/fpm/pool.d/
ADD docker/php/fpm/conf.d/99-app.ini ${PHP_CONF_DIR}/fpm/conf.d/
ADD docker/php/cli/conf.d/99-app.ini ${PHP_CONF_DIR}/cli/conf.d/

# Copie des scripts complémentaires à lancer au démarrage du container
COPY docker/entrypoint.d/* /entrypoint.d/

RUN a2ensite app app-ssl && \
    service php${PHP_VERSION}-fpm reload
