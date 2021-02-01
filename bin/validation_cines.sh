#!/bin/bash

##################################################################################################
#
#       Script d'appel du web service proposé par le site facile.cines.fr du CINES.
#
##################################################################################################
#
# Arguments :
#   -f|--file    : chemin vers le fichier à valider, OBLIGATOIRE.
#   -u|--url     : URL du web service, FACULTATIF, "https://facile.cines.fr/xml", par défaut.
#   -m|--maxtime : temps max d'exécution.
#   -t|--timeout : temps max de connexion.
#
##################################################################################################

DEFAULT_URL="https://facile.cines.fr/xml"

ARGS=`getopt -o "f:u:m:t:" -l "file:,url:,maxtime:,timeout:" -n "getopt.sh" -- "$@"`
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

    -t|--timeout)
    #---------------
      if [ -n "$2" ]; then
        timeout=$2
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
if [ -n "$maxtime" ]; then
    maxtime="--max-time $maxtime"
fi
if [ -n "$timeout" ]; then
    timeout="--connect-timeout $timeout"
fi

# Options de curl :
#   --insecure           : pas de vérification du certificat SSL, https://curl.se/docs/manpage.html#-k
#   --max-time 60        : temps max d'exécution d'1 minute, https://curl.se/docs/manpage.html#-m
#   --connect-timeout 10 : temps max de connexion de 10 secondes, https://curl.se/docs/manpage.html#--connect-timeout
#   --silent             : https://curl.se/docs/manpage.html#-s

#curl --silent --connect-timeout 10 --form file="@$file" $host
#curl --max-time 360 --form file="@$1" $host
curl $maxtime $timeout --form file="@$file" $url
