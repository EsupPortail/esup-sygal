<?php
namespace SygalApiImpl;

use Laminas\ApiTools\Provider\ApiToolsProviderInterface;
use Laminas\Stdlib\ArrayUtils;

class Module implements ApiToolsProviderInterface
{
    public function getConfig(): array
    {
        $configDir = __DIR__ . '/../config';

        return ArrayUtils::merge(
            include $configDir . '/module.config.php',
            include $configDir . '/versions/v1.config.php',
        );
    }

    public function getAutoloaderConfig(): array
    {
        return [
            'Laminas\ApiTools\Autoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ],
            ],
        ];
    }
}
