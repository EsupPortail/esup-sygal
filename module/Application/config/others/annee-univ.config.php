<?php

namespace Application;

use Application\Search\AnneeUniv\AnneeUnivSearchFilter;
use Application\Search\AnneeUniv\AnneeUnivSearchFilterFactory;
use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\AnneeUniv\AnneeUnivServiceFactory;

return [
    'search_filters' => [
        'factories' => [
            AnneeUnivSearchFilter::class => AnneeUnivSearchFilterFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            AnneeUnivService::class => AnneeUnivServiceFactory::class,
        ],
    ],
];
