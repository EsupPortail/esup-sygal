#!/usr/bin/env bash
set -e

#
# Script de génération des fichiers permettant de créer une base de données pour SyGAL.
#

usage() {
  echo "Usage :"
  echo "    $(basename $0) ARGUMENTS"
  echo "Arguments :"
  echo "    -c CONFIG_FILE Chemin du fichier de config. Obligatoire."
  echo "    -o OUTPUT_DIR  Chemin du répertoire où seront générés les scripts SQL. Obligatoire."
  exit 0
}

if [[ ${#} -eq 0 ]]; then
  usage
fi

while getopts ":c:o:" arg; do
  case "${arg}" in
  c)
    CONFIG_FILE="${OPTARG}"
    ;;
  o)
    OUTPUT_DIR="${OPTARG}"
    ;;
  ?)
    echo "Option invalide : -${OPTARG}."
    echo
    usage
    ;;
  esac
done

[[ -z "$CONFIG_FILE" ]] && echo ":-( Un fichier de config doit être spécifié en argument." && usage && exit 1
[[ -z "$OUTPUT_DIR" ]] && echo ":-( Un répertoire de destination doit être spécifié en argument." && usage && exit 1

mkdir -p $OUTPUT_DIR

[[ ! -f "$CONFIG_FILE" ]] && echo "Le fichier de config $CONFIG_FILE est introuvable" && exit 1
[[ ! -z "$(ls -A $OUTPUT_DIR)" ]] && echo "Le répertoire de destination $OUTPUT_DIR doit être vide" && exit 1

[[ -z $PGHOST ]] && echo "Vous devez faire un 'export PGHOST=xxxx'." && exit 1
[[ -z $PGPORT ]] && echo "Vous devez faire un 'export PGPORT=xxxx'." && exit 1
[[ -z $PGDATABASE ]] && echo "Vous devez faire un 'export PGDATABASE=xxxx'." && exit 1
[[ -z $PGUSER ]] && echo "Vous devez faire un 'export PGUSER=xxxx'." && exit 1
[[ -z $PGPASSWORD ]] && echo "Vous devez faire un 'export PGPASSWORD=xxxx'." && exit 1

# Chargement des variables de config :
#   DBNAME
#   DBUSER
source ${CONFIG_FILE}

THIS_DIR=$(cd $(dirname $0) && pwd)
OUTPUT_DIR=$(realpath "$OUTPUT_DIR")

echo "Base de données modèle :"
echo "  PGHOST=$PGHOST"
echo "  PGPORT=$PGPORT"
echo "  PGDATABASE=$PGDATABASE"
echo "  PGUSER=$PGUSER"
echo "Fichier de config chargé : $(realpath ${CONFIG_FILE})"
echo "Génération des fichiers permettant de créer la base de données '$DBNAME' et l'utilisateur '$DBUSER'..."

function replacePgDatabaseAndUserInScript() {
  FILE=$1
  # NB : d'abord le nom de la base puis le user
#  sed -i -e "s|$PGDATABASE|${DBNAME}|g" $FILE
  sed -i -e "s|OWNER TO postgres|OWNER TO ${DBUSER}|g" $FILE
  sed -i -e "s|OWNER TO $PGUSER|OWNER TO ${DBUSER}|g" $FILE
  sed -i -e "s|Owner: $PGUSER|Owner: ${DBUSER}|g" $FILE
}
function injectConfParamsInScript() {
  FILE=$1
  sed -i -e "s|{DBNAME}|${DBNAME}|g" $FILE
  sed -i -e "s|{DBUSER}|${DBUSER}|g" $FILE
}
function prepareScript() {
  FILE=$1
  #sed -i -e 's/public\.//g' $FILE  # suppression du nom de schém "public"
  sed -i -e 's/FOR EACH ROW EXECUTE FUNCTION/FOR EACH ROW EXECUTE PROCEDURE/g' $FILE  # 9.x compatible
}

#=========================================================================#

#
# README.md
#
OUTPUT_FILE=$OUTPUT_DIR/README.md
SRC_SCRIPT=$THIS_DIR/src/README.template.md
cp $SRC_SCRIPT $OUTPUT_FILE
injectConfParamsInScript $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# build_db_files.sh
#
OUTPUT_FILE=$OUTPUT_DIR/build_db_files.sh
SRC_SCRIPT=$THIS_DIR/src/build_db_files.template.sh
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# build_db_files.conf.dist
#
OUTPUT_FILE=$OUTPUT_DIR/build_db_files.conf.dist
SRC_SCRIPT=$THIS_DIR/src/build_db_files.template.conf.dist
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#=========================================================================#

NAME_CLEAR_DB='clear_db'
NAME_CREATE_DB_USER='create_db_user'
NAME_CREATE_SCHEMA='create_schema'
NAME_INSERT_BOOTSTRAP_DATA='insert_bootstrap_data'
NAME_INSERT_DATA='insert_data'
NAME_PREPARE_DATA='prepare_data'
NAME_PREPARE_SEQUENCES='prepare_sequences'
NAME_CREATE_COMUE='create_comue'
NAME_CREATE_CED='create_ced'
NAME_INIT='init'
NAME_CREATE_FIXTURE='create_fixture'
NAME_CREATE_CONSTRAINTS='create_constraints'

mkdir -p $OUTPUT_DIR/sql

##
## clearing
##
#OUTPUT_FILE=$OUTPUT_DIR/sql/$NAME_CLEAR_DB.sql
#SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_CLEAR_DB.gen.sql
#psql --tuples-only --output $OUTPUT_FILE <$SRC_SCRIPT
#echo "> $OUTPUT_FILE"

#
# db and user
#
OUTPUT_FILE=$OUTPUT_DIR/sql/admin/01_$NAME_CREATE_DB_USER.sql
SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_CREATE_DB_USER.template.sql
mkdir $OUTPUT_DIR/sql/admin
cp $SRC_SCRIPT $OUTPUT_FILE
injectConfParamsInScript $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# schema objects
#
OUTPUT_FILE=$OUTPUT_DIR/sql/02_$NAME_CREATE_SCHEMA.sql
pg_dump --section=pre-data --schema-only --exclude-table 'MV_INDICATEUR*' --exclude-table '*_SAV' >$OUTPUT_FILE
replacePgDatabaseAndUserInScript $OUTPUT_FILE
#prepareScript $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# bootstrap data
#
OUTPUT_FILE=$OUTPUT_DIR/sql/03_$NAME_INSERT_BOOTSTRAP_DATA.sql
SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_INSERT_BOOTSTRAP_DATA.template.sql
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# data
#
OUTPUT_FILE=$OUTPUT_DIR/sql/04_$NAME_INSERT_DATA.sql
pg_dump --data-only --column-inserts \
--table="admission_etat" \
--table="admission_type_validation" \
--table="categorie_privilege" \
--table="discipline_sise" \
--table="domaine_scientifique" \
--table="formation_enquete_categorie" \
--table="formation_enquete_question" \
--table="formation_etat" \
--table="import_observ" \
--table="information_langue" \
--table="nature_fichier" \
--table="notif" \
--table="pays" \
--table="privilege" \
--table="profil" \
--table="profil_privilege" \
--table="soutenance_etat" \
--table="soutenance_qualite" \
--table="soutenance_qualite_sup" \
--table="type_rapport" \
--table="type_structure" \
--table="type_validation" \
--table="unicaen_avis_type" \
--table="unicaen_avis_type_valeur" \
--table="unicaen_avis_type_valeur_complem" \
--table="unicaen_avis_valeur" \
--table="unicaen_parametre_categorie" \
--table="unicaen_parametre_parametre" \
--table="unicaen_renderer_macro" \
--table="unicaen_renderer_template" \
--table="version_fichier" \
--table="wf_etape" \
> $OUTPUT_FILE
replacePgDatabaseAndUserInScript $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# prepare data
#
OUTPUT_FILE=$OUTPUT_DIR/sql/05_$NAME_PREPARE_DATA.sql
SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_PREPARE_DATA.sql
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# prepare sequences
#
OUTPUT_FILE=$OUTPUT_DIR/sql/06_$NAME_PREPARE_SEQUENCES.sql
SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_PREPARE_SEQUENCES.sql
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# constraints
#
OUTPUT_FILE=$OUTPUT_DIR/sql/07_$NAME_CREATE_CONSTRAINTS.sql
pg_dump --section=post-data --schema-only --exclude-table="mv_indicateur_*" >$OUTPUT_FILE
replacePgDatabaseAndUserInScript $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# COMUE
#
OUTPUT_FILE=$OUTPUT_DIR/sql/08_$NAME_CREATE_COMUE.sql.dist
SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_CREATE_COMUE.template.sql
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# CED
#
OUTPUT_FILE=$OUTPUT_DIR/sql/09_$NAME_CREATE_CED.sql.dist
SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_CREATE_CED.template.sql
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# init
#
OUTPUT_FILE=$OUTPUT_DIR/sql/10_$NAME_INIT.sql.dist
SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_INIT.template.sql
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

#
# fixtures
#
OUTPUT_FILE=$OUTPUT_DIR/sql/11_$NAME_CREATE_FIXTURE.sql.dist
SRC_SCRIPT=$THIS_DIR/src/sql/$NAME_CREATE_FIXTURE.template.sql
cp $SRC_SCRIPT $OUTPUT_FILE
echo "> $OUTPUT_FILE"

echo 'Terminé.'
