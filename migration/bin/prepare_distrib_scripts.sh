#!/bin/bash

echo "Deprecated."
exit 1

THIS_DIR=$(cd `dirname $0` && pwd)
ROOT_DIR=$(cd ${THIS_DIR}/.. && pwd)

SCHEMA_DIR=${ROOT_DIR}/ora2pg/schema
DATA_DIR=${ROOT_DIR}/ora2pg/data

# répertoire cible
DISTRIB_DIR=${ROOT_DIR}/distrib

# création des répertoires
mkdir -p ${DISTRIB_DIR}
mkdir -p ${DISTRIB_DIR}/02-schema
mkdir -p ${DISTRIB_DIR}/02-schema/01-sequences
mkdir -p ${DISTRIB_DIR}/02-schema/02-tables
mkdir -p ${DISTRIB_DIR}/02-schema/03-constraints
mkdir -p ${DISTRIB_DIR}/02-schema/04-indexes
mkdir -p ${DISTRIB_DIR}/02-schema/05-triggers
mkdir -p ${DISTRIB_DIR}/02-schema/06-functions
mkdir -p ${DISTRIB_DIR}/02-schema/07-views
mkdir -p ${DISTRIB_DIR}/02-schema/08-mviews
mkdir -p ${DISTRIB_DIR}/04-data
mkdir -p ${DISTRIB_DIR}/05-fkeys

# schema
cp ${SCHEMA_DIR}/sequences/sequence.sql       ${DISTRIB_DIR}/02-schema/01-sequences/
cp ${SCHEMA_DIR}/tables/table.sql             ${DISTRIB_DIR}/02-schema/02-tables/
cp ${SCHEMA_DIR}/tables/CONSTRAINTS_table.sql ${DISTRIB_DIR}/02-schema/03-constraints/
cp ${SCHEMA_DIR}/tables/INDEXES_table.sql     ${DISTRIB_DIR}/02-schema/04-indexes/
cp ${SCHEMA_DIR}/triggers/*_trigger.sql       ${DISTRIB_DIR}/02-schema/05-triggers/
cp ${SCHEMA_DIR}/functions/*_function.sql     ${DISTRIB_DIR}/02-schema/06-functions/
cp ${SCHEMA_DIR}/views/*_view.sql             ${DISTRIB_DIR}/02-schema/07-views/
cp ${SCHEMA_DIR}/mviews/*_mview.sql           ${DISTRIB_DIR}/02-schema/08-mviews/
cp ${SCHEMA_DIR}/tables/FKEYS_table.sql       ${DISTRIB_DIR}/05-fkeys/

# data
cp ${DATA_DIR}/CATEGORIE_PRIVILEGE_data.sql   ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/DOMAINE_SCIENTIFIQUE_data.sql  ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/IMPORT_OBSERV_data.sql         ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/INFORMATION_data.sql           ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/NATURE_FICHIER_data.sql        ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/PRIVILEGE_data.sql             ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/PROFIL_data.sql                ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/PROFIL_PRIVILEGE_data.sql      ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/TYPE_STRUCTURE_data.sql        ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/TYPE_VALIDATION_data.sql       ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/VERSION_FICHIER_data.sql       ${DISTRIB_DIR}/04-data/
cp ${DATA_DIR}/WF_ETAPE_data.sql              ${DISTRIB_DIR}/04-data/


function processDirFiles() {
  DIR=$1
  SCRIPT_NAME=all.sql
  cd $DIR
#  for f in *.sql ; do
#    sed -i '1,8d' $f  # suppression des 8 premières lignes
#  done
  ls -1a *.sql | grep --invert-match $SCRIPT_NAME | sed 's/^/\\i /' > $SCRIPT_NAME
  #echo "Répertoire $DIR traité."
}

# uniquement des scripts de schema
#for f in ${DISTRIB_DIR}/02-schema/01-sequences/*.sql   ; do processFile $f ; done
#for f in ${DISTRIB_DIR}/02-schema/02-tables/*.sql      ; do processFile $f ; done
#for f in ${DISTRIB_DIR}/02-schema/03-constraints/*.sql ; do processFile $f ; done
#for f in ${DISTRIB_DIR}/02-schema/04-indexes/*.sql     ; do processFile $f ; done
#for f in ${DISTRIB_DIR}/02-schema/05-triggers/*.sql    ; do processFile $f ; done
#for f in ${DISTRIB_DIR}/02-schema/06-functions/*.sql   ; do processFile $f ; done
#for f in ${DISTRIB_DIR}/02-schema/07-views/*.sql       ; do processFile $f ; done
#for f in ${DISTRIB_DIR}/02-schema/08-mviews/*.sql      ; do processFile $f ; done
#for f in ${DISTRIB_DIR}/05-fkeys/*.sql                 ; do processFile $f ; done

processDirFiles ${DISTRIB_DIR}/02-schema/01-sequences
processDirFiles ${DISTRIB_DIR}/02-schema/02-tables
processDirFiles ${DISTRIB_DIR}/02-schema/03-constraints
processDirFiles ${DISTRIB_DIR}/02-schema/04-indexes
processDirFiles ${DISTRIB_DIR}/02-schema/05-triggers
processDirFiles ${DISTRIB_DIR}/02-schema/06-functions
processDirFiles ${DISTRIB_DIR}/02-schema/07-views
processDirFiles ${DISTRIB_DIR}/02-schema/08-mviews
processDirFiles ${DISTRIB_DIR}/05-fkeys
processDirFiles ${DISTRIB_DIR}/04-data

echo "Préparation des scripts de distrib terminée : $DISTRIB_DIR"

