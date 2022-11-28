<?php

namespace These\Controller\Plugin\Url;

use Interop\Container\ContainerInterface;
use These\Service\Url\UrlTheseService;

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
