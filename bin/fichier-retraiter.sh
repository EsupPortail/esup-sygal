#!/usr/bin/env bash

CURR_DIR=$(cd `dirname $0` && pwd)

cd $CURR_DIR

#destinataires=$1
#fichier=$2
#php ../public/index.php fichier retraiter --tester-archivabilite --notifier="${destinataires}" ${fichier}

args=$*
php ../public/index.php fichier retraiter ${args}