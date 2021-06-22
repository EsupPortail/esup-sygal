#!/bin/bash

THIS_DIR=$(cd `dirname $0` && pwd)
ROOT_DIR=$(cd ${THIS_DIR}/.. && pwd)

SCHEMA_DIR=${ROOT_DIR}/ora2pg/schema

function processFile() {
  #sed -i '1,8d' $1   # suppression des 8 premières lignes
  sed -i -e 's/"//g' $1   # suppression des guillemets
  sed -i -e 's/NULL * AS TYPE_STRUCTURE_DEPENDANT_ID/NULL::bigint AS TYPE_STRUCTURE_DEPENDANT_ID/g' $1

  sed -i -e 's/EST_ANNEXE = 0/EST_ANNEXE = false/g' $1
  sed -i -e 's/EST_EXPURGE = 0/EST_EXPURGE = false/g' $1
  sed -i -e 's/VERSION_CORRIGEE = 0/VERSION_CORRIGEE = false/g' $1
  sed -i -e 's/est_valide = 0/est_valide = false/g' $1
  sed -i -e 's/EST_VALIDE = 0/EST_VALIDE = false/g' $1
  sed -i -e 's/EST_CONFORME = 0/EST_CONFORME = false/g' $1
  sed -i -e 's/importable = 1/importable = true/g' $1
  sed -i -e 's/VERSION_CORRIGEE = 1/VERSION_CORRIGEE = true/g' $1
  sed -i -e 's/est_valide = 1/est_valide = true/g' $1
  sed -i -e 's/EST_VALIDE = 1/EST_VALIDE = true/g' $1
  sed -i -e 's/EST_CONFORME = 1/EST_CONFORME = true/g' $1
  sed -i -e 's/then 1 else 0 end atteignable/then true else false end atteignable/g' $1
  sed -i -e 's/then 1 else 0 end courante/then true else false end courante/g' $1

  echo "Fichier $f traité."
}

for f in ${SCHEMA_DIR}/functions/*_function.sql ; do processFile $f ; done
for f in ${SCHEMA_DIR}/mviews/*_mview.sql ;       do processFile $f ; done
for f in ${SCHEMA_DIR}/tables/*_table.sql ;       do processFile $f ; done
for f in ${SCHEMA_DIR}/triggers/*_trigger.sql ;   do processFile $f ; done
for f in ${SCHEMA_DIR}/views/*_view.sql ;         do processFile $f ; done

echo "Préparation des scripts terminée."
