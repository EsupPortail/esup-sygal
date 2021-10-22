<?php

namespace Soutenance;

use Soutenance\Service\Evenement\EvenementService;
use Soutenance\Service\Evenement\EvenementServiceFactory;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return array(
    'bjyauthorize' => [
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
            ],
        ],
    ],

    'router' => [
        'routes' => [
        ],
    ],

    'service_manager' => [
        'factories' => [
            EvenementService::class => EvenementServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
        ],
    ],
    'form_elements' => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'invokables' => [
        ],
    ],
);
