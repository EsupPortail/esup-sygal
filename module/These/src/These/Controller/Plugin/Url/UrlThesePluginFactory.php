<?php

namespace These\Controller\Plugin\Url;

use Application\Service\Url\UrlTheseService;
use Interop\Container\ContainerInterface;

class UrlThesePluginFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var UrlTheseService $urlService */
        $urlService = $container->get(UrlTheseService::class);

        $service = new UrlThesePlugin();
        $service->setUrlTheseService($urlService);

        return $service;
    }
}
