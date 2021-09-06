Personnalisations de l'application
==================================


Logo et lien de l'établissement
-------------------------------

Le pied des pages de l'application contient le logo de l'établissement sous la forme d'un lien emmenant vers
le site de l'établissement.

- Dans le fichier de config `${APPLICATION_ENV}.local.php`, adaptez le `'label'`, `'title'` et `'uri'` du lien
  mentionnant votre établissement dans le pied de page de l'application :

```php
    'navigation'   => [
        'default' => [
            'home' => [
                'pages' => [
                    'etab' => [
                        'label' => _("Normandie Université"),
                        'title' => _("Page d'accueil du site de Normandie Université"),
                        'uri'   => 'http://www.normandie-univ.fr',
                        'class' => 'logo-etablissement',
                        // NB : Spécifier la classe 'logo-etablissement' sur une page de navigation provoque le "remplacement"
                        //     du label du lien par l'image 'public/logo-etablissement.png' (à créer le cas échéant).
```

- Créez le fichier `public/logo-etablissement.png` correspondant au logo de votre établissement, il figurera dans
  le pied des pages de l'application.


Fil d'actualités sur la page d'accueil
------------------

Sur la page d'accueil, il est possible d'afficher des actualités fournies par une flux RSS .

- Dans le fichier de config `${APPLICATION_ENV}.local.php`, activez ou pas la consultation du flux et 
  renseignez son URL le cas échéant :

```php
    'actualite' => [
        'actif' => false,
        'flux' => "https://www.normandie-univ.fr/feed/?post_type=post&cat=406,448,472",
    ],
```


Page de couverture des thèses
-----------------------------

Il est possible de modifier l'apparence des pages de couverture PDF générées par l'application. Il s'agit de
substituer le template PHTML et la feuille de styles CSS par défaut par les vôtres.

Attention quand même, ne dérivez pas trop par rapport aux fichiers fournis et censurez vos ambitions esthétiques car 
la génération PDF a ses limites (cf. https://github.com/mpdf/mpdf).

- Dans le fichier de config `${APPLICATION_ENV}.local.php`, modifiez les chemins par défaut :

```php
    'sygal' => [
        //..
        'page_de_couverture' => [
            'template' => [
                // template .phtml
                'phtml_file_path' => APPLICATION_DIR . '/module/Application/src/Application/Service/PageDeCouverture/pagedecouverture.phtml',
                // feuille de styles
                'css_file_path' => APPLICATION_DIR . '/module/Application/src/Application/Service/PageDeCouverture/pagedecouverture.css',
            ],
        ],
```


Pages d'informations
--------------------

Accessibles uniquement depuis la page d'accueil.

TODO
