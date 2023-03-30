<?php

namespace Horodatage;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Formation\Provider\IdentityProvider;
use Formation\Provider\IdentityProviderFactory;
use Formation\Service\Notification\FormationNotificationFactory;
use Formation\Service\Notification\FormationNotificationFactoryFactory;
use Formation\Service\Url\UrlService;
use Formation\Service\Url\UrlServiceFactory;
use Formation\View\Helper\EtatViewHelper;
use Formation\View\Helper\FormateursViewHelper;
use Formation\View\Helper\ModaliteViewHelper;
use Formation\View\Helper\SeancesViewHelper;
use Formation\View\Helper\SessionInscriptionViewHelper;
use Formation\View\Helper\SessionLibelleViewHelper;
use Formation\View\Helper\SiteViewHelper;
use Formation\View\Helper\TypeViewHelper;
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
                    'Horodatage\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Horodatage/Entity/Db/Mapping',
                ],
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

    'view_helpers' => [
        'invokables' => [
        ]
    ],

    'public_files' => [
        'inline_scripts' => [
        ],
        'stylesheets' => [
        ],
    ],
);
