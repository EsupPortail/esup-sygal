<?php
/**
 * Configuration globale du module UnicaenApp.
 *
 * If you have a ./config/autoload/ directory set up for your project, 
 * drop this config file in it and change the values as you wish.
 */
$settings = [
    /**
     * Informations concernant cette application
     */
    'app_infos' => [
        'nom'     => "SoDoct",
        'desc'    => "SOutenance, Doctorat et Organisation du Circuit des Thèses",
        'version' => "2.0.1",
        'date'    => "27/11/2017",
        'contact' => [
            'mail' => "assistance-sodoct@unicaen.fr",
            //'tel' => "01 02 03 04 05",
        ],
        'mentionsLegales'        => "http://www.unicaen.fr/acces-direct/mentions-legales/",
        'informatiqueEtLibertes' => "http://www.unicaen.fr/acces-direct/informatique-et-libertes/",
    ],
    /**
     * Période d'exécution de la requête de rafraîchissement de la session utilisateur, en millisecondes.
     */
    'session_refresh_period' => 0, // 0 <=> aucune requête exécutée
];

/**
 * You do not need to edit below this line
 */
return [
    'unicaen-app' => $settings,
];