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

[[ -z $1 ]] && usage

################################################################################################################

SYGAL_DIR=$(cd `dirname $0` && pwd)

set -e

# Minimum vital
apt-get -qq update && \
apt-get install -y \
    git \
    nano

# Récupération de l'image Docker Unicaen et lancement de son Dockerfile.sh
export UNICAEN_IMAGE_TMP_DIR=/tmp/docker-unicaen-image
git clone https://git.unicaen.fr/open-source/docker/unicaen-image.git ${UNICAEN_IMAGE_TMP_DIR}
cd ${UNICAEN_IMAGE_TMP_DIR}
bash Dockerfile.sh ${PHP_VERSION}


cd ${SYGAL_DIR}

# NB: Variables d'env positionnées par ${UNICAEN_IMAGE_TMP_DIR}/Dockerfile.sh
# APACHE_CONF_DIR=/etc/apache2 \
# PHP_CONF_DIR=/etc/php/7.0

# Configuration Apache et FPM
cp docker/apache-ports.conf    ${APACHE_CONF_DIR}/ports.conf
cp docker/apache-site.conf     ${APACHE_CONF_DIR}/sites-available/sygal.conf
cp docker/apache-site-ssl.conf ${APACHE_CONF_DIR}/sites-available/sygal-ssl.conf
cp docker/fpm/pool.d/app.conf  ${PHP_CONF_DIR}/fpm/pool.d/sygal.conf
cp docker/fpm/conf.d/app.ini   ${PHP_CONF_DIR}/fpm/conf.d/sygal.ini

a2ensite sygal sygal-ssl && \
    service php7.0-fpm reload
