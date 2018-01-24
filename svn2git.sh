#!/usr/bin/env bash

#
# Pré-requis: Créer le fichier users.txt contenant les auteurs des commits svn,
# exemple :
# brossard = Stéphane Brossard <stephane.brossard@unicaen.fr>
# gauthierb = Bertrand Gauthier <bertrand.gauthier@unicaen.fr>
# metivier = Jean-Philippe Métivier <jean-philippe.metivier@unicaen.fr>

# Il est possible de créer une version (à compléter) de ce fichier comme ceci :
# svn log ^/ --xml | grep -P "^<author" | sort -u | perl -pe 's/<author>(.*?)<\/author>/$1 = /' > users.txt
#

sudo apt-get install subversion git git-svn

svnRepoUrl=$(svn info --show-item repos-root-url)
targetDir="/home/gauthierb/workspace/sodoct-git"
gitRepoUrl="git@git.unicaen.fr:dev/sodoct.git"

#svn log ^/ --xml | grep -P "^<author" | sort -u | perl -pe 's/<author>(.*?)<\/author>/$1 = /' > users.txt

git svn clone ${svnRepoUrl} --authors-file=users.txt --no-metadata -s ${targetDir}
cd ${targetDir}

git for-each-ref refs/remotes | cut -d / -f 3- | grep -v @ | \
    while read branchname; do \
        git branch "$branchname" "refs/remotes/$branchname"; \
        git branch -r -d "$branchname"; \
    done
git for-each-ref refs/remotes | cut -d / -f 3- | grep -v @ | \
    while read branchname; do \
        git branch "$branchname" "refs/remotes/$branchname"; \
        git branch -r -d "$branchname"; \
    done

git remote add origin ${gitRepoUrl}

git push -u origin --all
git push -u origin --tags