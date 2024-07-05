<?php

use Application\Controller\Factory\PaysControllerFactory;
use Application\Controller\PaysController;
use Application\Service\Pays\PaysService;
use Application\Service\Pays\PaysServiceFactory;
use Application\Service\TitreAcces\TitreAccesService;
use Application\Service\TitreAcces\TitreAccesServiceFactory;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [

        ],
    ],
    'service_manager' => [
        'factories' => [
            TitreAccesService::class => TitreAccesServiceFactory::class,
        ],
    ],
];
