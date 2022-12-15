<?php

namespace Depot\View\Helper\Url;

use Depot\Service\Url\UrlDepotService;
use Interop\Container\ContainerInterface;

class UrlDepotHelperFactory
{
    public function __invoke(ContainerInterface $container): UrlDepotHelper
    {
        /** @var UrlDepotService $urlService */
        $urlService = $container->get(UrlDepotService::class);

        $service = new UrlDepotHelper();
        $service->setUrlDepotService($urlService);

        return $service;
    }
}
