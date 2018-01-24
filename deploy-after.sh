#!/usr/bin/env bash

################################################################################################################################
#                                       Script post-déploiement.
################################################################################################################################

# Ce script a accès aux mêmes variables que le script "./deploy.sh" :
#   - host              ex: "root@ldev"
#   - www               ex: "/var/www"
#   - versionsdir       ex: "/var/www/sodoct-versions"
#   - name              ex: "sodoct"
#   - ts                ex: "20170317-110750"
#   - tmpdir            ex: "/var/www/sodoct-versions/tmp"
#   - destdir           ex: "/var/www/sodoct-versions/sodoct-20170317-110750"


### Création du lien "/etc/cron/cron.d/sodoct".
echo -ne "\e[34m"
echo -e ""
echo -e "> Màj du lien \e[4m/etc/cron.d/sodoct\e[24m => \e[4m$versionsdir/latest/data/cron/sodoct\e[24m ...."
echo -ne "\e[0m"
cmd="/bin/cp -f $versionsdir/latest/data/cron/sodoct.dist /etc/cron.d/sodoct && sed -i \"s={SODOCT_ROOT_DIR}=$versionsdir/latest=\" /etc/cron.d/sodoct"
echo "ssh $host $cmd"
ssh "$host" "$cmd"
