<?php

namespace Application;

use Application\Entity\Db\UtilisateurToken;

return [
    'unicaen-auth' => [
        /**
         * Configuration de l'authentification à l'aide d'un token dans la BDD de l'application.
         */
        'token' => [
            /**
             * Description facultative de ce mode d'authentification qui apparaîtra sur la page de connexion.
             */
            'description' => "Description ????",
        ],
    ],

    'unicaen-auth-token' => [
        /**
         * Classe d'entité Doctrine mappant un jeton utilistauer en base de données.
         */
        'user_token_entity_class' => UtilisateurToken::class,
    ],
];
