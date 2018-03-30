#!/bin/sh
#
# Script de déploiement destiné à être placé dans les hooks d'un dépôt intermediare git (--bare)
# sous le nom "update".
#
# Called by "git receive-pack" with arguments: refname sha1-old sha1-new
#

refname="$1"
oldrev="$2"
newrev="$3"

# --- Safety check
if [ -z "$GIT_DIR" ]; then
    echo "Don't run this script from the command line." >&2
    echo " (if you want, you could supply GIT_DIR then run" >&2
    echo "  $0 <ref> <oldrev> <newrev>)" >&2
    exit 1
fi

if [ -z "$refname" -o -z "$oldrev" -o -z "$newrev" ]; then
        echo "usage: $0 <ref> <oldrev> <newrev>" >&2
        exit 1
fi


BRANCH="master"

# only checking out the BRANCH branch
if [[ "$refname" = "refs/heads/$BRANCH" ]];
then
    echo "Ref $refname received. Deploying $BRANCH branch..."
else
    echo "Ref $refname received. Doing nothing: only the $BRANCH branch may be deployed on this server."
    exit 0
fi


# Répertoire de l'appli servi par Apache/Nginx
# (dépôt git dont l'origin pointe sur un dépôt intermédiaire)
appdir="/var/www/sygal"

unset GIT_DIR
cd "$appdir"

echo "-- Déploiement..."

# Checkout
git checkout "$BRANCH" -f

# Install config locale
srclocalconfigfile="/root/.ssh/sygal.config.local.php"
dstlocalconfigfile="$appdir/config/autoload/local.php"
cp "$srclocalconfigfile" "$dstlocalconfigfile" && \
echo "Config locale déployée : $srclocalconfigfile => $dstlocalconfigfile"

# Composer install
composer install --no-dev --optimize-autoloader

# Commandes Doctrine
vendor/bin/doctrine-module orm:clear-cache:query
vendor/bin/doctrine-module orm:clear-cache:metadata
vendor/bin/doctrine-module orm:clear-cache:result
vendor/bin/doctrine-module orm:generate-proxies

# Install config CRON
srccronconfigfile="$appdir/data/cron/sygal.dist"
dstcronconfigfile="/etc/cron.d/sygal"
cp -f "$srccronconfigfile" "$dstcronconfigfile" && sed -i "s={APP_ROOT_DIR}=$appdir=" "$dstcronconfigfile" && \
echo "Config CRON déployée : $srccronconfigfile => $dstcronconfigfile"

echo "-- Déploiement dans $appdir terminé."

exit 0
