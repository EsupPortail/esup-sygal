# Migrer un projet versionné par Subversion en un projet versionné par Git

## Installer git et git-svn

    $ sudo apt-get install subversion git git-svn

## Se placer dans le répertoire d'un projet sous Subversion

    $ cd ~/workspace/sodoct
    $ svn info --show-item repos-root-url      
    https://svn.unicaen.fr/svn/sodoct

## Fournir la correspondance entre utilisateurs Subversion et auteurs Git

Lancer la commande suivante qui génère un fichier "users.txt" :

    $ svn log ^/ --xml | grep -P "^<author" | sort -u | \
          perl -pe 's/<author>(.*?)<\/author>/$1 = /' > users.txt

Exemple de fichier *users.txt* généré :

    brossard = 
    gauthierb = 
    metivier = 
    
Compléter ce fichier à la main, exemple :

    brossard = Stéphane Brossard <stephane.brossard@unicaen.fr>
    gauthierb = Bertrand Gauthier <bertrand.gauthier@unicaen.fr>
    metivier = Jean-Philippe Métivier <jean-philippe.metivier@unicaen.fr>

## Créer un dépôt Git à partir du dépôt Subversion

    $ git svn clone https://svn.unicaen.fr/svn/sodoct \
        --authors-file=users.txt --no-metadata -s /home/gauthierb/workspace/sodoct-git
    $ cd /home/gauthierb/workspace/sodoct-git
    
## Déplacer les tags Subversion importés pour en faire de vrais tags Git

    $ git for-each-ref refs/remotes | cut -d / -f 3- | grep -v @ | \
        while read branchname; do \
            git branch "$branchname" "refs/remotes/$branchname"; \
            git branch -r -d "$branchname"; \
        done
    
## Déplacer les branches Subversion importées pour en faire de vraies branches Git

    $ git for-each-ref refs/remotes | cut -d / -f 3- | grep -v @ | \
        while read branchname; do \
            git branch "$branchname" "refs/remotes/$branchname"; \
            git branch -r -d "$branchname"; \
        done

## Ajouter le serveur Git comme serveur distant 
    
    $ git remote add origin https://git.unicaen.fr/bertrand.gauthier/sodoct.git

## Pousser le tout vers le serveur

    $ git push -u origin --all
    $ git push -u origin --tags 

