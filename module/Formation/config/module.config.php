<?php

namespace Formation;

use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
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
            FormationNotificationFactory::class => FormationNotificationFactoryFactory::class,
            IdentityProvider::class => IdentityProviderFactory::class,
            UrlService::class => UrlServiceFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'view_helpers' => [
        'invokables' => [
            'modalite' => ModaliteViewHelper::class,
            'site' => SiteViewHelper::class,
            'type' => TypeViewHelper::class,
            'etat' => EtatViewHelper::class,
            'seances' => SeancesViewHelper::class,
            'formateurs' => FormateursViewHelper::class,
            'sessionInscription' => SessionInscriptionViewHelper::class,
            'sessionLibelle' => SessionLibelleViewHelper::class,
        ]
    ],

    'public_files' => [
        'inline_scripts' => [
        ],
        'stylesheets' => [
            '071_formation' => '/css/formation.css',
        ],
    ],
);
