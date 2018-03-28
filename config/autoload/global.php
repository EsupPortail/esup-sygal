<?php

use Application\Navigation\NavigationFactoryFactory;
use Retraitement\Filter\Command\MinesCommand;

return array(
    'sygal' => [
        'archivabilite' => [
            'check_ws_script_path' => __DIR__ . '/../../bin/from_cines/check_webservice_response.sh',
            'script_path'          => __DIR__ . '/../../bin/validation_cines.sh',
        ],

        'retraitement' => [
            /**
             * Commande utilisée pour retraiter les fichiers PDF, et ses options.
             */
            'command' => [
                'class' => MinesCommand::class,
                'options' => [
                    'pdftk_path' => 'pdftk',
                    'gs_path' => 'gs',
//                    'gs_args' => '-dPDFACompatibilityPolicy=1'
                ],
            ],

            /**
             * Durée au bout de laquelle le retraitement est interrompu pour le relancer en tâche de fond.
             * Valeurs possibles: '30s', '1m', etc. (cf. "man timeout").
             */
            'timeout' => '20s',
        ],

        'notification' => [
            /**
             * Destinataires à ajouter systématiquement lors de l'envoi d'une notification.
             */
            'cc' => ['suivi-mail-sodoct@unicaen.fr'],
            //'bcc' => [],
        ],
    ],

    'service_manager' => array(
        'factories' => array(
            'navigation' => NavigationFactoryFactory::class,
        ),
    ),
);
