<?php

/**
 * Ce fichier de config n'est pertinent que pour le mode CLI.
 */
if (php_sapi_name() !== 'cli') {
    return  [];
}

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                /**
                 * Lorsqu'on lance la commande 'vendor/bin/doctrine-module orm:clear-cache:query' pour générer les proxies Doctrine,
                 * le module BjyAuthorize se charge (comme les autres, normal!) et interroge la BDD... donc a besoin des proxies Doctrine !
                 *
                 * Cela provoque une erreur du genre :
                 *      Fatal error: require(): Failed opening required 'data/DoctrineORMModule/Proxy/__CG__Xxxxx.php' (include_path='.:/usr/share/php')
                 *      in /app/vendor/doctrine/common/lib/Doctrine/Common/Proxy/AbstractProxyFactory.php on line 204
                 *
                 * Pour éviter cette erreur, on décide de forcer la génération des proxies ès lors qu'on est en mode CLI ! :-(
                 */
                'generate_proxies' => true,
            ],
        ],
    ],
];
