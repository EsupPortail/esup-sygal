#!/usr/bin/env bash

#
# Script d'install d'un serveur, traduction du Dockerfile.
#

usage() {
  cat << EOF
Script d'install d'un serveur, traduction du Dockerfile.
Usage: $0 <version de PHP>
EOF
  exit 0;
}

[[ -z "$1" ]] && usage

################################################################################################################

PHP_VERSION="$1"
SYGAL_DIR=$(cd `dirname $0` && pwd)

set -e

# Minimum vital
apt-get -qq update && \
apt-get install -y \
    git \
    nano \
    ghostscript-x \
    php${PHP_VERSION}-imagick \
    imagemagick

# Récupération de l'image Docker Unicaen et lancement de son Dockerfile.sh
export UNICAEN_IMAGE_TMP_DIR=/tmp/docker-unicaen-image
git clone https://git.unicaen.fr/open-source/docker/unicaen-image.git ${UNICAEN_IMAGE_TMP_DIR}
cd ${UNICAEN_IMAGE_TMP_DIR}
. Dockerfile.sh ${PHP_VERSION}


cd ${SYGAL_DIR}

# NB: Variables d'env exportées par ${UNICAEN_IMAGE_TMP_DIR}/Dockerfile.sh
# APACHE_CONF_DIR=/etc/apache2
# PHP_CONF_DIR="/etc/php/$1"

# Configuration Apache et FPM
cp docker/apache-ports.conf     ${APACHE_CONF_DIR}/ports.conf
cp docker/apache-site.conf      ${APACHE_CONF_DIR}/sites-available/app.conf
cp docker/apache-site-ssl.conf  ${APACHE_CONF_DIR}/sites-available/app-ssl.conf
cp docker/fpm/pool.d/www.conf   ${PHP_CONF_DIR}/fpm/pool.d/
cp docker/fpm/conf.d/90-app.ini ${PHP_CONF_DIR}/fpm/conf.d/

sed -i -re 's/SetEnv APPLICATION_ENV "(development|test)"/SetEnv APPLICATION_ENV "production"/' \
    ${APACHE_CONF_DIR}/sites-available/app-ssl.conf

a2ensite app app-ssl && \
    service apache2 reload && \
    service php${PHP_VERSION}-fpm reload
