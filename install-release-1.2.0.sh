#!/usr/bin/env bash

#
# DB upgrade
#
echo "Mise à jour de la base de données..."
php public/index.php run-sql-script --path=./data/SQL/release-1.2.0/10_fix.sql && \
php public/index.php run-sql-script --path=./data/SQL/release-1.2.0/20_refonte-fichier.sql && \
echo ":-) Base de données mise à jour avec succès !"
