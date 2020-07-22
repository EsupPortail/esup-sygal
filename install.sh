#!/usr/bin/env bash

#
# Script d'installation des pré-requis de l'application.
#
# Usages :
#   ./install.sh
#   ./install.sh dev

[ $1 != "dev" ] && nodev="--no-dev"

# Composer install
composer install ${nodev} --no-suggest --optimize-autoloader

# Répertoire pour l'upload de fichiers
mkdir -p upload && \
  chown -R www-data:root upload && \
  chmod -R 770 upload
