<?php

return [
    'view_manager' => [
        'template_map' => [
            'unicaen-authentification/auth/login' => __DIR__ . '/../../view/application/unicaen-authentification/auth/login.phtml',
            'unicaen-authentification/auth/login-tabs' => __DIR__ . '/../../view/application/unicaen-authentification/auth/login-tabs.phtml',
            'unicaen-authentification/auth/request-password-reset-form' => __DIR__ . '/../../view/application/unicaen-authentification/auth/request-password-reset-form.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];