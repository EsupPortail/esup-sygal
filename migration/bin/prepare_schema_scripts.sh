#!/bin/bash

THIS_DIR=$(cd `dirname $0` && pwd)
ROOT_DIR=$(cd ${THIS_DIR}/.. && pwd)

SCHEMA_DIR=${ROOT_DIR}/ora2pg/schema

function processFile() {
  #sed -i '1,8d' $1   # suppression des 8 premières lignes
  sed -i -e 's/"//g' $1   # suppression des guillemets
  sed -i -e 's/NULL * AS TYPE_STRUCTURE_DEPENDANT_ID/NULL::bigint AS TYPE_STRUCTURE_DEPENDANT_ID/g' $1
  echo "Fichier $f  traité."
}

for f in ${SCHEMA_DIR}/functions/*_function.sql ; do processFile $f ; done
for f in ${SCHEMA_DIR}/mviews/*_mview.sql ;       do processFile $f ; done
for f in ${SCHEMA_DIR}/tables/*_table.sql ;       do processFile $f ; done
for f in ${SCHEMA_DIR}/triggers/*_trigger.sql ;   do processFile $f ; done
for f in ${SCHEMA_DIR}/views/*_view.sql ;         do processFile $f ; done

echo "Préparation des scripts terminée."
