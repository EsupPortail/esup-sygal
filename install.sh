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

# Composer install
composer install --no-interaction --no-suggest --prefer-dist --optimize-autoloader

# Création ou vidange des répertoires de cache
mkdir -p data/cache && chmod -R 777 data/cache && rm -rf data/cache/*
mkdir -p data/DoctrineModule/cache && chmod -R 777 data/DoctrineModule/cache && rm -rf data/DoctrineModule/cache/*
mkdir -p data/DoctrineORMModule/Proxy && chmod -R 777 data/DoctrineORMModule/Proxy && rm -rf data/DoctrineORMModule/Proxy/*

vendor/bin/laminas-development-mode enable

vendor/bin/doctrine-module orm:generate-proxies
vendor/bin/doctrine-module orm:clear-cache:query
vendor/bin/doctrine-module orm:clear-cache:metadata
vendor/bin/doctrine-module orm:clear-cache:result

vendor/bin/laminas-development-mode disable
