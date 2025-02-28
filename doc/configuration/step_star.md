Module Step/Star
================

Généralités
-----------

Ce module permet l'envoi des thèses en préparation et soutenues vers les applications Step/Star
à l'aide du web service proposé par l'ABES.

https://documentation.abes.fr/aidethesespro/index.html#ImportsStepStar


Configuration de l'application
------------------------------

Fichier de config concerné : `config/autoload/{dev|test|prod}.secret.local.php`.

Vous devez adapter le contenu des la clé `step_star` :

```php
    /**
     * Config du module StepStar.
     */
    'step_star' => [
        //
        // Options pour l'appel du web service Step-Star.
        // https://documentation.abes.fr/aidethesespro/index.html#ImportsStepStar
        //
        'api' => [
            'soap_client' => [
                'wsdl' => [
                    // adresse du web service fourni par theses.fr (fournie par l'ABES)
                    'url' => 'https://host.theses.fr/path/to/DepotTEF.wsdl',
                ],
                'soap' => [
                    'version' => SOAP_1_1, // cf. extension "php-soap"
                    //'proxy_host' => 'proxy.domain.fr',
                    //'proxy_port' => 3128,
                ],
            ],
            'params' => [
                // identifiant STEP/STAR de l'établissement d'inscription (fourni par l'ABES)
                'idEtablissement' => $etablissementStepStar = 'XXXX',
            ],
        ],
        //
        // Options pour la génération du fichier XML intermédiaire (avant génération TEF).
        //
        'xml' => [
            // codes des types de financements correspondant à un contrat doctoral
            'codes_type_financ_contrat_doctoral' => [
                '1', // Contrat doctoral
                '2', // Contrat doctoral-mission d'enseignement
                '3', // Contrat doctoral avec autres missions
                'K', // 10-Contrat Doctoral : ministériel
                'L', // 14-Contrat Doctoral : financerment privé
                'M', // 30-Financement contrats recherche
                'Q', // 10-Contrat Doctoral : ministériel
                'R', // Contrat doctoral établissement
                'R', // Contrat Doctoral  Région 100 %
                'S', // 12-Contrat Doctoral : région co-financé
                'S', // Contrat doctoral ENSICAEN
                'T', // Contrat Doctoral  Autres organismes
                'T', // Contrat doctoral EPST
                'U', // 14-Contrat Doctoral : financerment privé
                'U', // Contrat doctoral autre organisme
                //'V', // Sans contrat doctoral
                'W', // Contrat Doctoral  Région 50%
                'W', // Contrat doctoral Région RIN 100%
                'Y', // Contrat Doctoral Etablissement
                'Y', // Contrat doctoral Région RIN 50%
            ],
            // codes des types de financements correspondant au dispositif CIFRE
            'codes_orig_financ_cifre' => [
                '31', // Conventions CIFRE
            ],
            // paramètres concernant la section "partenaires de recherche"
            'params_partenaire_recherche' => [
                'libelle' => "Établissement co-accrédité",
            ],
        ],
        //
        // Options pour la génération des fichiers au format TEF.
        //
        'tef' => [
            // paramètres nécessaires à la génération du fichier XSL à partir du template twig
            'xsl_template_params' => [
                // identifiant STEP/STAR de l'établissement (identique à 'api.params.idEtablissement')
                'etablissementStepStar' => $etablissementStepStar,
                // identifiant "autorité SUDOC" de l'établissement de soutenance
                'autoriteSudoc_etabSoutenance' => '1234567890',
            ],
            // préfixe des répertoires temporaires créés lors de la génération
            'output_dir_path_prefix' => '/tmp/sygal_stepstar_',
            // faut-il supprimer les répertoires/fichiers temporaires après la génération ?
            'clean_after_work' => false,
        ],
    ],
```


Programmation des envois
------------------------

Fichier concerné (sur le serveur d'application) : `/etc/cron.d/sygal`.

Vous *pouvez* adapter cette partie du fichier si besoin :

```php
##### Envoi vers STEP-STAR ######
0 6 * * 1-5 root ETAB=$ETAB $APP_DIR/bin/run-envoi-step-star.sh >/tmp/cron-sygal-run-envoi-step-star.log 2>&1
```

Vous verrez dans le script bash `bin/run-envoi-step-star.sh` les thèses envoyées :
- theses sans date de soutenance reelle"
- theses dont la date de soutenance est dans les 3 prochains mois"
- theses dont la date de soutenance est passee de 7j maximum"

Vous n'avez pas la main sur les critères de sélection des thèses envoyées.
Sauf si vous réécrivez un script différent (lancez `/usr/bin/php ${APP_DIR}/public/index.php` et cherchez la section 
"StepStar" pour connaître les options de la commande `step-star:envoyer-theses`).


Envois ponctuels
----------------

### En ligne de commande

Vous pouvez lancer manuellement le script bash `bin/run-envoi-step-star.sh` pour déclencher l'envoi des thèses.

En regardant dans ce script, vous trouverez les lignes de commandes utilisées pour envoyer des thèses.
Exemple pour envoyer les thèses en cours dont la date de soutenance est dans les 3 prochains mois :
```bash
/usr/bin/php ${APP_DIR}/public/index.php step-star:envoyer-theses --etablissement ${ETAB} --tag ${TAG} --etat E --date-soutenance-min +P1D --date-soutenance-max +P3M
```


### Dans l'interface graphique

Il est aussi possible sur la page _Administration > STEP-STAR > Envoi de thèses_ d'envoyer n'importe quelle thèse.



Logs
----

Quelle qe soit la façon d'envoyer les thèses, des logs d'exécution sont produits et enregistrés en base de données. 
Ils sont accessibles via la page _Administration > STEP-STAR > Logs_.
