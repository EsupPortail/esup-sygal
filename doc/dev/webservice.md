Web service d'import
====================

Prise en compte d'un nouveau champ provenant du web service
-----------------------------------------------------------

**NB: il existe le pendant de cette documentation côté web service.**

Ex: un nouveau champ `ine` apparaît dans le service Doctorant.


- Désactivez l'exécution périodique (cron) du script de remplissage des tables `SYGAL_*` 
  à partir des vues `V_SYGAL_*`.  
  Normalement, ça se joue sur le serveur où est installé le web service dans le fichier 
  `/etc/cron.d/sygal-import-ws-cron`.

- Désactivez si besoin l'import périodique *qui impacterait la base de données sur laquelle vous allez intervenir*.
  Normalement, ça se passe sur le serveur de l'application dans le fichier `/etc/cron.d/sygal`.

- En base de données :

    - Ajouter la nouvelle colonne `INE` dans la table `TMP_DOCTORANT` receptacle des données provenant du web service.

    - Ajouter la nouvelle colonne `INE` dans la table finale `DOCTORANT`.

    - Corriger la vue `SRC_DOCTORANT` pour prendre en compte la nouvelle colonne `TMP_DOCTORANT.INE` et ainsi
      permettre la mise à jour de cette colonne dans la table finale `DOCTORANT`.
  
- Dans les sources PHP :

    - Mettre à jour le mapping `TmpDoctorant` dans le fichier  
      `module/Import/src/Import/Model/Mapping/Import.Model.TmpDoctorant.dcm.xml` 
      Puis ajouter la nouvelle propriété `$ine` dans la classe d'entité   
      `module/Import/src/Import/Model/TmpDoctorant.php`
      
    - Mettre à jour le mapping `Doctorant` dans le fichier  
      `module/Application/src/Application/Entity/Db/Mapping/Application.Entity.Db.Doctorant.dcm.xml` 
      Puis ajouter la nouvelle propriété `$ine` ainsi que ses getter et setter associés dans la classe d'entité   
      `module/Application/src/Application/Entity/Db/Doctorant.php`
       
- Dans l'interface graphique de l'application :

    - Aller dans le menu "Import" pour vérifier la version du web service (API) interrogé par l'application
      pour l'établissement concerné.
  
    - Aller dans le menu "Synchro" puis cliquer sur 
      "Mise à jour des vues différentielles et des procédures de mise à jour".
  
      Ensuite Cliquer sur "Tableau de bord principal" et vérifier que la nouvelle colonne apparaît bien dans la table 
      destination et que la colonne "Import actif" est coché.
      
- En ligne de commande :

    - Vérifier d'abord que le champ `ine` figure bien dans la réponse retournée par le web service, ex :
      `curl --insecure --header "Accept: application/json" --header "Authorization: Basic c3lnYWwtYXBwOmF6ZXJ0eQ==" https://localhost:8443/doctorant`
      
    - Lancer l'application dans Docker :
      `docker-compose up sygal`

    - Lancer dans un premier temps seulement l'import des données provenant du web service 
      (`--synchronize=0` pour ne pas déclencher la synchronisation après l'import) :
      `docker-compose exec sygal php public/index.php import --service=doctorant --etablissement=UCN --synchronize=0`
      
    - Vérifier que la table `TMP_DOCTORANT` a bien été peuplée, notamment la colonne `INE`.
    
    - Lancer ensuite la synchronisation (des tables `TMP_*` vers les tables finales) :
      `docker-compose exec sygal php public/index.php synchronize --service=doctorant`
      
    - Vérifier que la colonne `INE` a bien été peuplée dans la table finale `DOCTORANT`.

- Réactivez si besoin l'import périodique.
