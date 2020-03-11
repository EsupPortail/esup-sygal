#!/usr/bin/env bash

#
# Script de génération d'une partie des scripts SQL permettant de créer de zéro la base de données de SyGAL.
#
# Arguments :
#   $1 : Nom du schéma de base de données. Ex: "SYGAL". Obligatoire.
#
#        NB: Attention, cet argument n'a pas la vocation de "sélectionner" le schéma de base de données à utiliser,
#            il est simplement exploité pour les noms de fichiers SQL générés.
#            Le schéma de base de données utilisé est celui spécifié dans la config Doctrine de l'appli
#            (cf. fichier `config/autoload/*.secret.local.php` de l'appli).
#
# Exemple d'usage :
#   bash gen-db-scripts.sh "SYGAL"
#

[[ -z "$1" ]] && echo "Le nom du schéma de base de données doit être spécifié, ex: SYGAL." && exit 1

SCHEMA=$1

# fonction effaçant ou commentant certaines options de création de séquence dans le(s) fichier(s) spécifié(s)
commentSeqOptionsInFile() {
    for f in $1; do
        sed -i 's/\(START WITH [0-9]\+\)//ig; s/\(CACHE [0-9]\+\)/\/*\1*\//ig' ${f}
    done
}
# fonction remplaçant 'SYGAL.' par '/*SYGAL.*/' et '"SYGAL".' par '/*"SYGAL".*/' dans le(s) fichier(s) spécifié(s)
commentPrefixInFile() {
    for f in $1; do
        sed -i "s/\(${SCHEMA}\.\)/\/*\1*\//ig" ${f}
        sed -i "s/\(\"${SCHEMA}\"\.\)/\/*\1*\//ig" ${f}
    done
}
# fonction remplaçant par exemple "/*HISTO::*/1234" par "$2/*SUBSTIT*/" dans le(s) fichier(s) spécifié(s) en $1
replaceHistoIdInFile() {
    for f in $1; do
        sed -i "s/\/\*HISTO::\*\/[0-9]\+/$2\/\*SUBSTIT\*\//ig" ${f}
    done
}

FROM_FILE_PATH_CLEAR="/tmp/oracle-clear-schema-${SCHEMA}.sql"
FROM_FILE_PATH_SCHEMA="/tmp/oracle-generate-schema-${SCHEMA}-from-${SCHEMA}.sql"
FROM_FILE_PATH_INSERTS="/tmp/inserts"
FROM_FILE_PATH_REF="/tmp/oracle-generate-ref-constraints-${SCHEMA}-from-${SCHEMA}.sql"

TABLES="CATEGORIE_PRIVILEGE,DOMAINE_SCIENTIFIQUE,IMPORT_OBSERV,INFORMATION,NATURE_FICHIER,PRIVILEGE,PROFIL,PROFIL_PRIVILEGE,TYPE_STRUCTURE,TYPE_VALIDATION,VERSION_FICHIER,WF_ETAPE"
HISTO_ID_SUBSTIT=1 # valeur forcée pour les colonnes d'historique

rm -rf ${FROM_FILE_PATH_INSERTS} && mkdir -p ${FROM_FILE_PATH_INSERTS}

php public/index.php generate-script-for-schema-clearing \
    --connection=doctrine.connection.orm_default && \
\
php public/index.php generate-script-for-schema-creation \
    --src-connection=doctrine.connection.orm_default \
    --dst-connection=doctrine.connection.orm_default \
    --ref-constraints-included=0 && \
\
php public/index.php generate-scripts-for-data-inserts \
    --src-connection=doctrine.connection.orm_default \
    --dst-connection=doctrine.connection.orm_default \
    --tables=${TABLES} \
    --output-dir=${FROM_FILE_PATH_INSERTS} && \
\
php public/index.php generate-script-for-ref-constraints-creation \
    --src-connection=doctrine.connection.orm_default \
    --dst-connection=doctrine.connection.orm_default && \
\
commentPrefixInFile     ${FROM_FILE_PATH_CLEAR} && \
commentPrefixInFile     ${FROM_FILE_PATH_SCHEMA} && \
commentSeqOptionsInFile ${FROM_FILE_PATH_SCHEMA} && \
commentPrefixInFile     "${FROM_FILE_PATH_INSERTS}/*" && \
replaceHistoIdInFile    "${FROM_FILE_PATH_INSERTS}/*" ${HISTO_ID_SUBSTIT} && \
commentPrefixInFile     ${FROM_FILE_PATH_REF}
