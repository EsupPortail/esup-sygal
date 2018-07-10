###########################################################################################
#
#                               Image pour le dev.
#
#         Montage des sources attendu dans le volume "/webapp" du container.
#
###########################################################################################

FROM unicaen-dev-php7.0-apache

LABEL maintainer="Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>"

ENV APACHE_CONF_DIR=/etc/apache2 \
    PHP_CONF_DIR=/etc/php/7.0

## Installation de packages requis.
RUN apt-get install -y \
        php7.0-imagick

# Nettoyage
RUN apt-get autoremove -y && apt-get clean && rm -rf /tmp/* /var/tmp/*

# Symlink apache access and error logs to stdout/stderr so Docker logs shows them
RUN ln -sf /dev/stdout /var/log/apache2/access.log
RUN ln -sf /dev/stdout /var/log/apache2/other_vhosts_access.log
RUN ln -sf /dev/stderr /var/log/apache2/error.log

# Config PHP.
ADD docker/php.conf ${PHP_CONF_DIR}/fpm/conf.d/30-php-sygal.ini

# Configuration Apache et FPM
ADD docker/apache-ports.conf    ${APACHE_CONF_DIR}/ports.conf
ADD docker/apache-site.conf     ${APACHE_CONF_DIR}/sites-available/sygal.conf
ADD docker/apache-site-ssl.conf ${APACHE_CONF_DIR}/sites-available/sygal-ssl.conf
ADD docker/fpm/pool.d/app.conf  ${PHP_CONF_DIR}/fpm/pool.d/sygal.conf

RUN a2ensite sygal sygal-ssl && \
    service php7.0-fpm reload
