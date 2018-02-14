#!/usr/bin/env bash

#destinataires=$1
#fichier=$2
#php ../public/index.php fichier retraiter --tester-archivabilite --notifier="${destinataires}" ${fichier}

args=$*
php ../public/index.php fichier retraiter ${args}