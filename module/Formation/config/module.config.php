<?php

namespace Formation;

use Application\Navigation\ApplicationNavigationFactory;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Controller\AvisController;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Provider\Privilege\IndexPrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Membre\MembreServiceFactory;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceFactory;
use Soutenance\Service\Validation\ValidationService;
use Soutenance\Service\Validation\ValidationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return array(
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                ],
            ],
        ],
    ],

    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Formation\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Formation/Entity/Db/Mapping',
                ],
            ],
        ],

        //todo remove ?
        'connection' => [
            'orm_default' => [
                'driver_class' => OCI8::class,
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'public_files' => [
        'inline_scripts' => [
        ],
        'stylesheets' => [
        ],
    ],
);
