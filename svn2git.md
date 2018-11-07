# Créer un dépôt Git à partir d'un dépôt Subversion existant

## Installer git et git-svn

    $ sudo apt-get install subversion git git-svn

## Se placer dans le répertoire d'un projet sous Subversion

    $ cd ~/workspace/sodoct
    $ svn info --show-item repos-root-url
    https://svn.unicaen.fr/svn/sodoct

## Fournir la correspondance entre utilisateurs Subversion et auteurs Git

Lancer la commande suivante qui génère un fichier `users.txt` :

    $ svn log ^/ --xml | grep -P "^<author" | sort -u | \
          perl -pe 's/<author>(.*?)<\/author>/$1 = /' > users.txt

Exemple de fichier `users.txt` généré :

    brossard = 
    gauthierb = 
    metivier = 
    
Compléter ce fichier à la main, exemple :

    brossard = Stéphane Brossard <stephane.brossard@unicaen.fr>
    gauthierb = Bertrand Gauthier <bertrand.gauthier@unicaen.fr>
    metivier = Jean-Philippe Métivier <jean-philippe.metivier@unicaen.fr>

## Créer un dépôt Git local à partir du dépôt Subversion distant

    $ git svn clone https://svn.unicaen.fr/svn/sodoct \
        --authors-file=users.txt --no-metadata -s /home/gauthierb/workspace/sodoct-git

## Se positionner dans le répertoire du dépôt Git local ainsi créé 
    
    $ cd /home/gauthierb/workspace/sodoct-git
    
## Ajouter la remote "origin" au dépôt local
    
    $ git remote add origin https://git.unicaen.fr/bertrand.gauthier/sodoct.git
    
## Lister les tags Subversion importés

Lister les tags Subversion importés :

    $ git for-each-ref refs/remotes/origin/tags | cut -d / -f 5- | grep -v @ | 
        while read tagname; do
            echo "$tagname"; 
	done

NB: le nom des tags affichés ici ne doit pas contenir `origin/` ; si c'est le cas, ajuster `refs/remotes/origin/tags` et `5-`.

## Déplacer les tags Subversion importés pour en faire de vrais tags Git

    $ git for-each-ref refs/remotes/origin/tags | cut -d / -f 5- | grep -v @ | 
        while read tagname; do
            git tag "$tagname" "origin/tags/$tagname"; git branch -r -d "origin/tags/$tagname";
	done
    
## Lister les branches Subversion importées

    $ git for-each-ref refs/remotes/origin | cut -d / -f 4- | grep -v @ | \
        while read branchname; do \
            echo "$branchname"; \
        done

NB: le nom des branches affichées ici ne doit pas contenir `origin/` ; si c'est le cas, ajuster `refs/remotes/origin/tags` et `5-`.
    
## Déplacer les branches Subversion importées pour en faire de vraies branches Git

    $ git for-each-ref refs/remotes/origin | cut -d / -f 4- | grep -v @ | \
        while read branchname; do \
            git branch "$branchname" "refs/remotes/origin/$branchname"; \
            git branch -r -d "origin/$branchname"; \
        done

## Pousser le tout vers le serveur

    $ git push -u origin --all && \
      git push -u origin --tags 

