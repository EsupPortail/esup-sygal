<?php

namespace These;

use Laminas\Router\Http\Segment;
use These\Controller\DomaineHalSaisieController;
use These\Controller\Factory\DomaineHalSaisieControllerFactory;
use These\Form\DomaineHalSaisie\DomaineHalSaisieForm;
use These\Form\DomaineHalSaisie\DomaineHalSaisieFormFactory;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalFieldset;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalFieldsetFactory;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalHydrator;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalHydratorFactory;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => DomaineHalSaisieController::class,
                    'action' => [
                        'saisie-domaine-hal',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_MODIFICATION_DOMAINES_HAL_THESE,
                    ],
                ]
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'these' => [
                'child_routes'  => [
                    'saisie-domaine-hal' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'    => '/saisie-domaine[/:these]',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                /** @see DomaineHalSaisieController::saisieDomaineHalAction() */
                                'controller'    => DomaineHalSaisieController::class,
                                'action'        => 'saisie-domaine-hal',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            DomaineHalSaisieController::class => DomaineHalSaisieControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            DomaineHalSaisieForm::class => DomaineHalSaisieFormFactory::class,
            DomaineHalFieldset::class => DomaineHalFieldsetFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            DomaineHalHydrator::class => DomaineHalHydratorFactory::class,
        ],
    ],
];
