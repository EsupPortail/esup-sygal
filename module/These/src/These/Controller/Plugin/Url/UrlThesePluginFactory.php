<?php

namespace These\Controller\Plugin\Url;

use These\Service\Url\UrlTheseService;
use Interop\Container\ContainerInterface;

class UrlThesePluginFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var UrlTheseService $urlService */
        $urlService = $container->get(\These\Service\Url\UrlTheseService::class);

        $service = new UrlThesePlugin();
        $service->setUrlTheseService($urlService);

        return $service;
    }
}
