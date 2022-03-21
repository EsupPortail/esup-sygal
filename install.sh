#!/usr/bin/env bash

#
# Script d'installation des pré-requis de l'application POUR LA PROD.
#
# Usages :
#   ./install.sh

CURDIR=$(cd `dirname $0` && pwd)

cd ${CURDIR}

# Répertoire pour l'upload de fichiers
mkdir -p upload && \
  chown -R www-data:root upload && \
  chmod -R 770 upload

# Composer install
composer install --no-suggest --prefer-dist --optimize-autoloader

# Désactivation du mode "développement"
vendor/bin/laminas-development-mode disable
