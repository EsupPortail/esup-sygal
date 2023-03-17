<?php

namespace Application;

use Application\Provider\Privilege\FinancementPrivileges;
use Application\View\Helper\FinancementFormatterHelperFactory;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'OrigineFinancement' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            FinancementPrivileges::FINANCEMENT_VOIR_ORIGINE_NON_VISIBLE,
                        ],
                        'resources'  => ['OrigineFinancement'],
                    ],
                ],
            ],
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'financementFormatter' => FinancementFormatterHelperFactory::class,
        ],
    ],
];
