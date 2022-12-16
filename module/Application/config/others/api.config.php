<?php

namespace Application;

use Laminas\ApiTools\Documentation as ApiToolsDocumentation;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => [
                        // Api Documentation
                        ApiToolsDocumentation\Controller::class,
                    ],
//                    'privileges' => [
//                        AdministrationPrivileges::API_DOCUMENTATION,
//                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
];