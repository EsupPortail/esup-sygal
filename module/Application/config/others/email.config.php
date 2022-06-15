<?php

namespace Application;

use Application\Service\Email\EmailTheseService;
use Application\Service\Email\EmailTheseServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            EmailTheseService::class => EmailTheseServiceFactory::class,
        ],
    ],
];
