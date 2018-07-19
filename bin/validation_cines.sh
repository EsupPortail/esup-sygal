#!/bin/bash

##########################################################################################
#       Script d'appel du web service proposé par le site facile.cines.fr du CINES.
##########################################################################################
# Arguments :
#   1/ le chemin vers le fichier à valider, OBLIGATOIRE.
#   2/ l'URL du web service, FACULTATIF ("https://facile.cines.fr/xml", par défaut).
##########################################################################################

#export http_proxy=proxy.unicaen.fr:3128
#export https_proxy=proxy.unicaen.fr:3128

DEFAULT_URL="https://facile.cines.fr/xml"

ARGS=`getopt -o "f:u:m:" -l "file:,url:,maxtime:" -n "getopt.sh" -- "$@"`
if [ $? -ne 0 ];
then
  exit 1
fi
eval set -- "$ARGS"
while true;
do
  case "$1" in
    -f|--file)
    #--------------
      if [ -n "$2" ]; then
        file="$2"
      fi
      shift 2;;

    -u|--url)
    #---------------
      if [ -n "$2" ]; then
        url=$2
      fi
      shift 2;;

    -m|--maxtime)
    #---------------
      if [ -n "$2" ]; then
        maxtime=$2
      fi
      shift 2;;

    --)
      shift
      break;;
  esac
done

if [ ! "$file" ]; then
    printf "Aucun fichier spécifié!"
    exit 1
fi

if [ ! "$url" ]; then
    url="$DEFAULT_URL"
fi
maxtime=""
if [ -n "$maxtime" ]; then
    maxtime="--max-time $maxtime"
fi

# "-k"             : désactive la vérification du certificat SSL
# "--max-time 600" : spécifie un temps maximum d'exécution de 5 minutes

#curl --silent --connect-timeout 10 --form file="@$file" $host
#curl --max-time 360 --form file="@$1" $host
curl $maxtime --form file="@$file" $url