<?php

namespace Application;

use Application\Service\Email\EmailService;
use Application\Service\Email\EmailServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            EmailService::class => EmailServiceFactory::class,
        ],
    ],
];
