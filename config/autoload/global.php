<?php

use Application\Navigation\NavigationFactoryFactory;
use Retraitement\Filter\Command\CinesCommand;
use Retraitement\Filter\Command\MinesCommand;

return array(
    'sodoct' => [
        'archivabilite' => [
            'check_ws_script_path' => __DIR__ . '/../../bin/from_cines/check_webservice_response.sh',
            'script_path'          => __DIR__ . '/../../bin/validation_cines.sh',
        ],
        'retraitement' => [
            'command' => [
                'class' => MinesCommand::class,
                'options' => [
                    'pdftk_path' => 'pdftk',
                    'gs_path' => 'gs',
//                    'gs_args' => '-dPDFACompatibilityPolicy=1'
                ],
            ],
        ],
        'notification' => [
            // destinataires à mettre systématiquement en copie cachée des mails
            'bcc' => ['suivi-mail-sodoct@unicaen.fr'],
        ],
    ],
    //'translator' => array(
        //'locale' => 'fr_FR',
    //),
    'service_manager' => array(
        'factories' => array(
            'navigation' => NavigationFactoryFactory::class,
        ),
    ),
);
