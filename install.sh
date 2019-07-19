#!/usr/bin/env bash

#
# This script runs required operations in order to set up the application.
#

# Composer install
composer install --no-dev --no-suggest --optimize-autoloader

# mpdf/mpdf/ttfontdata dir access
chown -R www-data:root vendor/mpdf/mpdf/ttfontdata && chmod -R 770 vendor/mpdf/mpdf/ttfontdata

# Répertoire d'upload par défaut
mkdir -p upload && chown -R www-data:root upload && chmod -R 770 upload

# Répertoires de travail de Doctrine
mkdir -p data/cache                   && chmod -R 777 data/cache
mkdir -p data/DoctrineModule/cache    && chmod -R 777 data/DoctrineModule/cache
mkdir -p data/DoctrineORMModule/Proxy && chmod -R 777 data/DoctrineORMModule/Proxy
rm -rf data/cache/*
rm -rf data/DoctrineModule/cache/*
rm -rf data/DoctrineORMModule/Proxy/*

# Commandes Doctrine
vendor/bin/doctrine-module orm:clear-cache:query
vendor/bin/doctrine-module orm:clear-cache:metadata
vendor/bin/doctrine-module orm:clear-cache:result
vendor/bin/doctrine-module orm:generate-proxies



#
# Install 1.2.1
#

mv upload/information upload/divers
