#!/usr/bin/env bash

CURR_DIR=$(cd `dirname $0` && pwd)

cd $CURR_DIR

args=$*
php ../public/index.php fichier fusionner ${args}