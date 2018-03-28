#!/usr/bin/env bash

################################################################################################################################
#                                       Script post-déploiement.
################################################################################################################################

# Ce script a accès aux mêmes variables que le script "./deploy.sh" :
#   - host              ex: "root@ldev"
#   - www               ex: "/var/www"
#   - versionsdir       ex: "/var/www/sygal-versions"
#   - name              ex: "sygal"
#   - ts                ex: "20170317-110750"
#   - tmpdir            ex: "/var/www/sygal-versions/tmp"
#   - destdir           ex: "/var/www/sygal-versions/sygal-20170317-110750"


### Création du lien "/etc/cron/cron.d/sygal".
echo -ne "\e[34m"
echo -e ""
echo -e "> Copie du fichier de config CRON \e[4m$versionsdir/latest/data/cron/sygal.dist\e[24m vers \e[4m/etc/cron.d/sygal\e[24m ...."
echo -ne "\e[0m"
cmd="/bin/cp -f $versionsdir/latest/data/cron/sygal.dist /etc/cron.d/sygal && sed -i \"s={APP_ROOT_DIR}=$versionsdir/latest=\" /etc/cron.d/sygal"
echo "ssh $host $cmd"
ssh "$host" "$cmd"
