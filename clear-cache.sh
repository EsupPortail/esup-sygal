#!/usr/bin/env bash

#
# Script de vidage des divers caches applicatifs.
#
# Usages :
#   ./clear-cache.sh
#

set -o errexit

DIR=$(cd `dirname $0` && pwd)

#
# Cache Laminas
#
LAMINAS_CACHE_DIR=${DIR}/data/cache
[[ -d $LAMINAS_CACHE_DIR ]] && \
printf "Vidage du répertoire du cache Laminas '%s'... " $LAMINAS_CACHE_DIR && \
rm -rf $LAMINAS_CACHE_DIR/* && \
echo "Fait."

#
# Cache Doctrine
#
DOCTRINE_CACHE_DIR=${DIR}/data/DoctrineModule/cache
[[ -d $DOCTRINE_CACHE_DIR ]] && \
printf "Vidage du répertoire du cache Doctrine '%s'... " $DOCTRINE_CACHE_DIR && \
rm -rf $DOCTRINE_CACHE_DIR/* && \
echo "Fait."
${DIR}/vendor/bin/doctrine-module orm:clear-cache:query
${DIR}/vendor/bin/doctrine-module orm:clear-cache:metadata
${DIR}/vendor/bin/doctrine-module orm:clear-cache:result
