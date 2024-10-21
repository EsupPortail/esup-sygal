<?php

namespace Formation;

use Horodatage\Service\Horodatage\HorodatageService;
use Horodatage\Service\Horodatage\HorodatageServiceFactory;
use Horodatage\View\Helper\DernierHorodatageViewHelper;
use Horodatage\View\Helper\DerniersHorodatagesViewHelper;
use Horodatage\View\Helper\HorodatagesParTypesViewHelper;
use Horodatage\View\Helper\HorodatagesParTypeViewHelper;
use Horodatage\View\Helper\HorodatageViewHelper;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
            ],
        ],
    ],

    'router'          => [
        'routes' => [
        ],
    ],

    'service_manager' => [
        'factories' => [
            HorodatageService::class => HorodatageServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
        ],
    ],
    'form_elements' => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'factories' => [
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'horodatage' => HorodatageViewHelper::class,
            'dernierHorodatage' => DernierHorodatageViewHelper::class,
            'derniersHorodatages' => DerniersHorodatagesViewHelper::class,
            'horodatagesParType' => HorodatagesParTypeViewHelper::class,
            'horodatagesParTypes' => HorodatagesParTypesViewHelper::class,
        ],
    ],

];