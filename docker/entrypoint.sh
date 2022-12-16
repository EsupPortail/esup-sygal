#!/usr/bin/env bash

if [[ -z ${PHP_VERSION} ]]; then \
    echo "Variable PHP_VERSION non définie, impossible de continuer."
    exit 1
fi

# Exécute les scripts situés dans le dossier /entrypoint.d :
ENTRYPOINTS_DIR=/entrypoint.d
if [[ -d ${ENTRYPOINTS_DIR} ]]; then
  /bin/run-parts --verbose ${ENTRYPOINTS_DIR}
fi

# Démarre php-fpm puis apache2
# (NB: Apache doit être lancé au premier plan sinon le processus Docker se termine)
mkdir -p /var/run/apache2
. /etc/apache2/envvars
startApache() {
  if [[ -z ${WITHOUT_DOCKER} ]]; then
    /usr/sbin/apache2ctl -D FOREGROUND
  else
    /usr/sbin/apache2ctl start
  fi
}
service php${PHP_VERSION}-fpm start && startApache
