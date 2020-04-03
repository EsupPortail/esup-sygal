#!/usr/bin/env bash

#
# Script de génération d'une partie des scripts SQL permettant de créer de zéro la base de données de SyGAL.
#
# Arguments :
#   Aucun.
#
# Exemple d'usage :
#   bash generate-database-scripts.sh
#

echo "
####################################################################################################
#
#  Génération d'une partie des scripts SQL permettant de créer de zéro la base de données de SyGAL
#
####################################################################################################"

THIS_DIR=$(cd `dirname $0` && pwd)
SCHEMA="SYGAL"

# répertoire de destination des résultats
OUTPUT_DIR=/tmp/sygal-doc/database
[[ -d ${OUTPUT_DIR} ]] && echo "Le répertoire de destination existe déjà : ${OUTPUT_DIR}" && exit 1
mkdir -p ${OUTPUT_DIR}

# copie et lancement du script dans le container docker
docker cp ${THIS_DIR}/docker/gen-db-scripts.sh sygal-container:/app && \
docker exec -it -w /app sygal-container bash gen-db-scripts.sh ${SCHEMA} && \
docker exec -it -w /app sygal-container rm gen-db-scripts.sh

FROM_FILE_PATH_CLEAR="/tmp/oracle-clear-schema-${SCHEMA}.sql"
FROM_FILE_PATH_SCHEMA="/tmp/oracle-generate-schema-${SCHEMA}-from-${SCHEMA}.sql"
FROM_FILE_PATH_INSERTS="/tmp/inserts"
FROM_FILE_PATH_REF="/tmp/oracle-generate-ref-constraints-${SCHEMA}-from-${SCHEMA}.sql"

TO_FILE_PATH_CLEAR="${OUTPUT_DIR}/00-oracle-clear-schema-${SCHEMA}.sql"
TO_FILE_PATH_SCHEMA="${OUTPUT_DIR}/00-oracle-generate-schema-${SCHEMA}.sql"
TO_FILE_PATH_INSERTS="${OUTPUT_DIR}/02-inserts"
TO_FILE_PATH_REF="${OUTPUT_DIR}/03-oracle-generate-ref-constraints-${SCHEMA}.sql"

# récupération des scripts SQL générés
docker cp sygal-container:${FROM_FILE_PATH_CLEAR}       ${TO_FILE_PATH_CLEAR} && \
docker cp sygal-container:${FROM_FILE_PATH_SCHEMA}      ${TO_FILE_PATH_SCHEMA} && \
docker cp sygal-container:${FROM_FILE_PATH_INSERTS}/.   ${TO_FILE_PATH_INSERTS} && \
docker cp sygal-container:${FROM_FILE_PATH_REF}         ${TO_FILE_PATH_REF}

echo
echo "> Terminé."
echo "> Résultat dans ${OUTPUT_DIR}."
