<?php

return [
    'unicaen-app' => [
        /**
         * Informations concernant l'application.
         */
        'app_infos' => [
            'nom'     => "SYGAL",
            'desc'    => "SYstème de Gestion et d'Accompagnement doctoraL",
            'version' => "0.0.0",
            'date'    => "27/11/2017",
            'contact' => [
                'mail' => "bertrand.gauthier@unicaen.fr",
//                'tel' => "01 02 03 04 05",
            ],
            'mentionsLegales'        => "http://www.unicaen.fr/acces-direct/mentions-legales/",
            'informatiqueEtLibertes' => "http://www.unicaen.fr/acces-direct/informatique-et-libertes/",
        ],

        /**
         * Période d'exécution de la requête de rafraîchissement de la session utilisateur, en millisecondes.
         */
        'session_refresh_period' => 0, // 0 <=> aucune requête exécutée
    ]
];
