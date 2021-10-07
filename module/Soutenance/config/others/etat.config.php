<?php

namespace Soutenance;

use Soutenance\Assertion\JustificatifAssertion;
use Soutenance\Assertion\JustificatifAssertionFactory;
use Soutenance\Controller\JustificatifController;
use Soutenance\Controller\JustificatifControllerFactory;
use Soutenance\Form\Justificatif\JusticatifHydrator;
use Soutenance\Form\Justificatif\JustificatifForm;
use Soutenance\Form\Justificatif\JustificatifFormFactory;
use Soutenance\Form\Justificatif\JustificatifHydratorFactory;
use Soutenance\Provider\Privilege\JustificatifPrivileges;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Justificatif\JustificatifServiceFactory;
use Soutenance\View\Helper\EtatViewHelper;
use Soutenance\View\Helper\JustificatifViewHelper;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Zend\Router\Http\Segment;

return [


    'service_manager' => [
        'factories' => [
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
        'factories' => [
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'etatSoutenance' => EtatViewHelper::class,
        ],
    ],
];