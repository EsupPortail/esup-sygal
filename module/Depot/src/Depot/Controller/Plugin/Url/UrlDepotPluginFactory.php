<?php

namespace Depot\Controller\Plugin\Url;

use Depot\Service\Url\UrlDepotService;
use Interop\Container\ContainerInterface;

class UrlDepotPluginFactory
{
    public function __invoke(ContainerInterface $container): UrlDepotPlugin
    {
        /** @var UrlDepotService $urlService */
        $urlService = $container->get(UrlDepotService::class);

        $service = new UrlDepotPlugin();
        $service->setUrlDepotService($urlService);

        return $service;
    }
}
