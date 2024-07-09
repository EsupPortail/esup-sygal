#!/usr/bin/env bash

#
# Script d'installation des pré-requis de l'application POUR LA PROD.
#
# Usages :
#   ./install.sh

set -o errexit

CURDIR=$(cd `dirname $0` && pwd)

cd ${CURDIR}

# Répertoire pour l'upload de fichiers
mkdir -p upload && \
  chown -R www-data:root upload && \
  chmod -R 770 upload

# Installation des dépendances PHP
composer install --no-interaction --prefer-dist --optimize-autoloader

# Cache Laminas
mkdir -p data/cache && chmod 777 data/cache
rm -rf data/cache/*

# Cache Doctrine
mkdir -p data/DoctrineModule/cache && chmod 777 data/DoctrineModule/cache && rm -rf data/DoctrineModule/cache/*
mkdir -p data/DoctrineORMModule/Proxy && chmod 777 data/DoctrineORMModule/Proxy && rm -rf data/DoctrineORMModule/Proxy/*
vendor/bin/laminas-development-mode enable  # nécessaire !
vendor/bin/doctrine-module orm:generate-proxies
vendor/bin/doctrine-module orm:clear-cache:query
vendor/bin/doctrine-module orm:clear-cache:metadata
vendor/bin/doctrine-module orm:clear-cache:result
vendor/bin/laminas-development-mode disable
