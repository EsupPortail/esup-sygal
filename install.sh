#!/usr/bin/env bash

#
# Script d'installation des pré-requis de l'application POUR LA PROD.
#
# Usages :
#   ./install.sh

CURDIR=$(cd `dirname $0` && pwd)

cd ${CURDIR}

# Composer install
composer install --no-dev --no-suggest --prefer-dist --optimize-autoloader && \

# Génération du fichier de version
echo "" && \
echo "Génération du fichier de version..." && \
./create-version-config-file && \

# Répertoire pour l'upload de fichiers
mkdir -p upload && \
  chown -R www-data:root upload && \
  chmod -R 770 upload
