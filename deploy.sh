#!/usr/bin/env bash

################################################################################################################################
#                                       Script de déploiement complet d'appli.
################################################################################################################################

# Actions :
#   - Création sur le serveur d'un répertoire de destination horodaté dans lequel sera copié le contenu du répertoire courant
#     (ex: "/var/www/monappli-versions/monappli-AAAMMJJ-HHMMSS").
#   - Création au même niveau d'un lien symbolique "latest" pointant sur lui
#     (ex: "/var/www/monappli-versions/latest").
#   - Reprise des fichiers de config locaux de la version déployée précédente
#     (ex: "/var/www/monappli/config/autoload/*local.php" => "/var/www/monappli-versions/monappli-AAAMMJJ-HHMMSS/config/autoload/").
#   - Reprise du fichier .htaccess de la version précédente déployée
#     (ex: "/var/www/monappli/public/.htaccess" => "/var/www/monappli-versions/monappli-AAAMMJJ-HHMMSS/public/").
#   - Génération des proxies Doctrine, vidange des caches Doctrine.
#   - Reload Apache pour vider tout le cache APC éventuel.
#   - Publication de la nouvelle version en créant un lien symbolique vers le nouveau répertoire
#     (ex: /var/www/monappli ==> /var/www/monappli-versions/monappli-AAAMMJJ-HHMMSS).
#   - Conservation des N dernières versions déployées seulement.
#   - Exécution du script "./deploy-after.sh" éventuel.

# Arguments requis :
#   -h|--host : compte@serveur de destination ("root@ldev.unicaen.fr" par exemple)
#   -w|--www  : chemin absolu du répertoire www sur le serveur ("/var/www" par exemple)
#
# Arguments facultatifs :
#   --include-local-config : Inclure les fichiers de config config/autoload/*local.php et public/.htaccess
#   -N : valeur de N pour Conservation des N dernières versions déployées seulement (5 par défaut)

################################################################################################################################

excludeLocalConfig="--exclude config/autoload/*local.php --exlude public/.htaccess"
N=5

# Nom du répertoire de l'appli : c'est le nom du répertoire courant sans le chemin.
# NB: il doit s'appeler pareil sur le serveur.
name=${PWD##*/}

# Execute getopt
ARGS=`getopt -o "h:w:N:" -l "host:,www:,N:,include-local-config" -n "getopt.sh" -- "$@"`
# Bad arguments
if [ $? -ne 0 ];
then
  exit 1
fi
# A little magic
eval set -- "$ARGS"

# Now go through all the options
while true;
do
  case "$1" in
    -h|--host)
    #--------------
      if [ -n "$2" ]; then
        host="$2"
      fi
      shift 2;;

    -w|--www)
    #---------------
      if [ -n "$2" ]; then
        www=$2
      fi
      shift 2;;

    --include-local-config)
    #---------------
      excludeLocalConfig=""
      shift 1;;

    -N)
    #---------------
      if [ -n "$2" ]; then
        N=$2
      fi
      shift 2;;

    --)
      shift
      break;;
  esac
done

echo -ne "\e[94m"
echo -e ""
echo -e "--------------------------------------------------------------------"
echo -e "   ____  _____ ____  _     ___ ___ _____ __  __ _____ _   _ _____   "
echo -e "  |  _ \| ____|  _ \| |   / _ \_ _| ____|  \/  | ____| \ | |_   _|  "
echo -e "  | | | |  _| | |_) | |  | | | | ||  _| | |\/| |  _| |  \| | | |    "
echo -e "  | |_| | |___|  __/| |__| |_| | || |___| |  | | |___| |\  | | |    "
echo -e "  |____/|_____|_|   |_____\___/___|_____|_|  |_|_____|_| \_| |_|    "
echo -e "                                                                    "
echo -e "--------------------------------------------------------------------"
echo -ne "\e[0m"

### Demande des arguments requis manquant.
if [ ! "$host" ]; then
    echo -e "Host (ex: root@ldev) ? "
    read host
fi
if [ ! "$www" ]; then
    echo -e "WWW dir (ex: /var/www) ? "
    read www
fi

echo -e ""
echo -e "  Host    : $host"
echo -e "  Www dir : $www"
echo -e "  N       : $N"

versionsdir="$www/$name-versions"
ts=$(date +"%Y%m%d-%H%M%S")
tmpdir="$versionsdir/tmp"

### Répertoire de destination, ex: "/var/www/monappli-versions/monappli-AAAMMJJ-HHMMSS".
destdir="$versionsdir/$name-$ts"

### Vérification que le répertoire des versions successives existe bien.
ssh "$host" "if [ ! -d "$versionsdir" ]; then exit 1; fi"
if [ $? -ne 0 ]; then
  echo -e ""
  echo -e "\e[31mLe répertoire $versionsdir n'existe pas sur le serveur !\e[0m"
  echo -e ""
  exit 1
fi


### Copie vers le répertoire temporaire du serveur.
### NB: on utilise un répertoire temporaire pour 2 raisons :
###   - ne pas créer un répertoire monappli-AAAMMJJ-HHMMSS incomplet si la synchro échoue.
###   - accélérer la prochaine tentative si la 1ere échoue.
echo -ne "\e[34m"
echo -e ""
echo -e "> Synchronisation du répertoire courant avec \e[4m$host:$tmpdir\e[24m ..."
echo -e "  (fichier des exclusions: ./deploy-ignore.txt)"
echo -ne "\e[0m"
rsync -az --perms --delete $excludeLocalConfig --exclude-from "./deploy-ignore.txt" -e ssh . "$host:$tmpdir"

### Stop si erreur.
if [ $? -ne 0 ]; then
  echo -e "\e[31mErreur bloquante rencontrée lors de la synchronisation !\e[0m"
  echo -e ""
  exit 1
fi


### Renommage du répertoire temporaire
echo -ne "\e[34m"
echo -e ""
echo -e "> Renommage du répertoire $tmpdir en $destdir\e[24m ..."
ssh "$host" mv "$tmpdir" "$destdir"
if [ $? -ne 0 ]; then
  echo -e ""
  echo -e "\e[31mErreur bloquante rencontrée lors du renommage de $tmpdir en $destdir sur le serveur !\e[0m"
  echo -e ""
  exit 1
fi


### Création du lien "latest" indiquant la dernière copie sur le serveur.
echo -ne "\e[34m"
echo -e ""
echo -e "> Màj du lien \e[4m$versionsdir/latest\e[24m => \e[4m$destdir\e[24m ...."
echo -ne "\e[0m"
cmd="rm $versionsdir/latest ; ln -sf $destdir $versionsdir/latest"
ssh "$host" "$cmd"


if [ "$excludeLocalConfig" ]; then
    ### Reprise des fichiers de config locaux de la version mise en ligne.
    echo -ne "\e[34m"
    echo -e ""
    echo -e "> Reprise des fichiers de config \e[4m$www/$name/config/autoload/*local.php\e[24m ..."
    echo -e "\e[0m"
    cmd="cp -v --backup=numbered $www/$name/config/autoload/*local.php $destdir/config/autoload/"
    ssh "$host" "$cmd"

    ### Avertissement si erreur.
    if [ $? -ne 0 ]; then
      echo -e ""
      echo -e "\e[95mProblème rencontré lors de la reprise des fichiers de config !\e[0m"
    fi


    ### Reprise du .htaccess de la version mise en ligne.
    echo -ne "\e[34m"
    echo -e ""
    echo -e "> Reprise du fichier \e[4m$www/$name/public/.htaccess\e[24m ..."
    echo -e "\e[0m"
    cmd="cp -v $www/$name/public/.htaccess $destdir/public/.htaccess"
    ssh "$host" "$cmd"

    ### Avertissement si erreur.
    if [ $? -ne 0 ]; then
      echo -e ""
      echo -e "\e[95mProblème rencontré lors de la reprise du .htaccess !\e[0m"
    fi
fi


### Lancement des commandes Doctrine.
echo -ne "\e[34m"
echo -e ""
echo -e "> Lancement des commandes Doctrine ..."
echo -e "\e[0m"
cmd=""
cmd="$cmd $destdir/vendor/bin/doctrine-module orm:clear-cache:query ; "
cmd="$cmd $destdir/vendor/bin/doctrine-module orm:clear-cache:metadata ; "
cmd="$cmd $destdir/vendor/bin/doctrine-module orm:clear-cache:result ; "
cmd="$cmd $destdir/vendor/bin/doctrine-module orm:generate-proxies"
ssh "$host" "$cmd"

### Stop si erreur.
if [ $? -ne 0 ]; then
  echo -e "\e[31mErreur bloquante rencontrée lors de la génération des proxies !\e[0m"
  echo -e ""
  exit 1
fi


### Reload apache.
### NB: Les commandes "doctrine-module orm:clear-cache:*" échouent si le cache est de type APC.
###     Solution: "service apache2 reload" vide tout le cache APC.
echo -ne "\e[34m"
echo -e ""
echo -e "> Apache reload ..."
echo -e "\e[0m"
cmd="service apache2 reload"
ssh "$host" "$cmd"

### Stop si erreur.
if [ $? -ne 0 ]; then
  echo -e "\e[31mErreur bloquante rencontrée lors du reload apache !\e[0m"
  echo -e ""
  exit 1
fi


### Déploiement proprement dit, i.e. màj du lien symbolique
### (ex: /var/www/monappli ==> /var/www/monappli-versions/monappli-AAAMMJJ-HHMMSS).
echo -ne "\e[34m"
echo -e ""
echo -e "> Màj du lien \e[4m$www/$name\e[24m => \e[4m$destdir\e[24m ..."
echo -ne "\e[0m"
cmd="rm $www/$name ; ln -sf $destdir $www/$name"
ssh "$host" "$cmd"

### Stop si erreur.
if [ $? -ne 0 ]; then
  echo -e "\e[31mErreur bloquante rencontrée lors de la màj du lien \e[4m$www/$name\e[24m !\e[0m"
  echo -e ""
  exit 1
fi


### Conservation des N dernières versions déployées seulement.
echo -ne "\e[34m"
echo -e ""
echo -e "> Conservation des $N dernières versions déployées seulement..."
echo -ne "\e[0m"
listDirCmd="ls -d /var/www/$name-versions/$name-* | sort --reverse"
i=1
dirsToRemove=''
for f in `ssh "$host" "$listDirCmd"`; do
  if ((i > N)); then
    dirsToRemove="$dirsToRemove $f"
  else
    echo "$f"
  fi
  i=$((i+1))
done
cmd="rm -rf $dirsToRemove"
ssh "$host" "$cmd"


### Exécution du script "./deploy-after.sh" éventuel.
echo -ne "\e[34m"
echo -e ""
echo -e "> Recherche du script post-déploiement "./deploy-after.sh" éventuel..."
####
if [ -f ./deploy-after.sh ]; then
    echo -ne "\e[34m"
    echo -e ""
    echo -e "-----------< Script post-déploiement >-----------"

    . ./deploy-after.sh

    echo -ne "\e[34m"
    echo -e ""
    echo -e "------------------------------------------------"
else
    echo -e ""
    echo -e "\e[95mAucun script './deploy-after.sh' trouvé. Vous pouvez en créer un et y placer les commandes à exécuter après le déploiement.\e[0m"
fi


echo -ne "\e[34m"
echo -e ""
echo -e "> Terminé!"
echo -e "\e[0m"
