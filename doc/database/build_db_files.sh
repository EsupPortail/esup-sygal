#!/usr/bin/env bash

#
# Script bash de préparation des scripts SQL permettant de créer une base de données pour ESUP-SyGAL.
#

usage() {
  echo "Usage :"
  echo "    $(basename $0) ARGUMENTS"
  echo "Arguments :"
  echo "    -c CONFIG_FILE Chemin du fichier de config. Obligatoire."
  echo "    -i INPUT_DIR   Chemin du répertoire où se trouvent les scripts SQL à customiser. Obligatoire."
  exit 0
}

if [[ ${#} -eq 0 ]]; then
  usage
fi

while getopts ":c:i:" arg; do
  case "${arg}" in
  c)
    CONFIG_FILE="${OPTARG}"
    ;;
  i)
    INPUT_DIR="${OPTARG}"
    ;;
  ?)
    echo "Option invalide : -${OPTARG}."
    echo
    usage
    ;;
  esac
done

[[ -z "$CONFIG_FILE" ]] && echo ":-( Un fichier de config doit être spécifié." && usage && exit 1
[[ ! -f "$CONFIG_FILE" ]] && echo "Le fichier de config $CONFIG_FILE est introuvable" && exit 1

[[ -z "$INPUT_DIR" ]] && echo ":-( Le répertoire où se trouvent les scripts SQL doit être spécifié." && usage && exit 1
[[ ! -d "$INPUT_DIR" ]] && echo ":-( $INPUT_DIR n'est pas un répertoire." && usage && exit 1
[[ ! -d "$INPUT_DIR/01_admin" ]] && echo ":-( Le répertoire $INPUT_DIR doit contenir un sous-répertoire 01_admin." && usage && exit 1
[[ ! -d "$INPUT_DIR/02_other" ]] && echo ":-( Le répertoire $INPUT_DIR doit contenir un sous-répertoire 02_other." && usage && exit 1


# Chargement des variables de config
source ${CONFIG_FILE}
echo "Fichier de config chargé : $(realpath ${CONFIG_FILE})"

THIS_DIR=$(cd $(dirname $0) && pwd)
INPUT_DIR=$(realpath "$INPUT_DIR")

function injectEtabParamsInScript() {
  FILE=$1
  sed -i -e "s|{ETAB_CODE}|${ETAB_CODE}|g" $FILE
  sed -i -e "s|{ETAB_SIGLE}|${ETAB_SIGLE}|g" $FILE
  sed -i -e "s|{ETAB_LIBELLE}|${ETAB_LIBELLE}|g" $FILE
  sed -i -e "s|{ETAB_DOMAINE}|${ETAB_DOMAINE}|g" $FILE
  sed -i -e "s|{EMAIL_ASSISTANCE}|${EMAIL_ASSISTANCE}|g" $FILE
  sed -i -e "s|{EMAIL_BIBLIOTHEQUE}|${EMAIL_BIBLIOTHEQUE}|g" $FILE
  sed -i -e "s|{EMAIL_DOCTORAT}|${EMAIL_DOCTORAT}|g" $FILE
  sed -i -e "s|{ETAB_COMUE}|${ETAB_COMUE}|g" $FILE
  sed -i -e "s|{ETAB_COMUE_SIGLE}|${ETAB_COMUE_SIGLE}|g" $FILE
  sed -i -e "s|{ETAB_COMUE_LIBELLE}|${ETAB_COMUE_LIBELLE}|g" $FILE
  sed -i -e "s|{ETAB_COMUE_DOMAINE}|${ETAB_COMUE_DOMAINE}|g" $FILE
  sed -i -e "s|{ETAB_CED}|${ETAB_CED}|g" $FILE
  sed -i -e "s|{ETAB_CED_SIGLE}|${ETAB_CED_SIGLE}|g" $FILE
  sed -i -e "s|{ETAB_CED_LIBELLE}|${ETAB_CED_LIBELLE}|g" $FILE
  sed -i -e "s|{SOURCE_APOGEE}|${SOURCE_APOGEE}|g" $FILE
  sed -i -e "s|{SOURCE_PHYSALIS}|${SOURCE_PHYSALIS}|g" $FILE
  sed -i -e "s|{TEST_USER_NOM_PATRONYMIQUE}|${TEST_USER_NOM_PATRONYMIQUE}|g" $FILE
  sed -i -e "s|{TEST_USER_PRENOM}|${TEST_USER_PRENOM}|g" $FILE
  sed -i -e "s|{TEST_USER_EMAIL}|${TEST_USER_EMAIL}|g" $FILE
  sed -i -e "s|{TEST_USER_PASSWORD_RESET_TOKEN}|${TEST_USER_PASSWORD_RESET_TOKEN}|g" $FILE
}

echo "Préparation des scripts SQL situés dans $INPUT_DIR..."

for file in $INPUT_DIR/*/*.sql.dist; do
  [ ! -f "$file" ] && echo ":-( Aucun fichier .sql.dist trouvé." && break
  newfile="${file%.sql.dist}.sql"
  cp -- "$file" "$newfile"
  injectEtabParamsInScript $newfile
  echo "> $file ==> $(basename $newfile)"
done