#!/usr/bin/env bash

#############################################################################################
#        Script de lancement de l'envoi vers STEP/STAR pour un établissement donné,
#                   ayant vocation à être lancé tous les jours.
#############################################################################################
#
# Variables d'env attendues :
#   ETAB : Code de l'établissement à traiter (ex: "UCN"). OBLIGATOIRE.
#

usage() {
  cat << EOF
Script de lancement de l'envoi vers STEP/STAR pour un établissement.
Usage: ETAB=<etab> $0
EOF
  exit 0;
}

echo "Etablissement : $ETAB"

[[ -z "$ETAB" ]] && usage

CURR_DIR=$(cd `dirname $0` && pwd)
APP_DIR=$(cd ${CURR_DIR}/.. && pwd)

echo "Répertoire courant : $CURR_DIR"
echo "Répertoire de l'appli : $APP_DIR"

# Avorte si le script est déjà en cours d'exécution (avec les mêmes arguments)
LOCK_FILE=/run/$(basename $0)_${ETAB}.lock
echo "Lock file : $LOCK_FILE"
exec 200>$LOCK_FILE
flock -n 200
if [ $? -eq 1 ]; then
  echo "Script $(realpath $0) déjà en cours d'exécution ($LOCK_FILE). Stop !"
  exit 1
fi

echo

TAG="cron-${ETAB}-$(date +%Y%m%d_%H%M%S)"

echo "> Envoi des theses ${ETAB} sans date de soutenance reelle..."
/usr/bin/php ${APP_DIR}/public/index.php step-star:envoyer-theses --etablissement ${ETAB} --tag ${TAG} --date-soutenance-null
echo
echo
echo "> Envoi des theses ${ETAB} dont la date de soutenance est dans les 3 prochains mois..."
/usr/bin/php ${APP_DIR}/public/index.php step-star:envoyer-theses --etablissement ${ETAB} --tag ${TAG} --date-soutenance-min +P1D --date-soutenance-max +P3M
echo
echo
echo "> Envoi forcé des theses ${ETAB} dont la date de soutenance est passee de 7j maximum..."
/usr/bin/php ${APP_DIR}/public/index.php step-star:envoyer-theses --etablissement ${ETAB} --tag ${TAG} --date-soutenance-min -P7D --date-soutenance-max -P1D --force
echo

