<?php

namespace Application\View\Helper\Url;

use Application\Service\Url\UrlTheseService;
use Interop\Container\ContainerInterface;

class UrlTheseHelperFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var UrlTheseService $urlService */
        $urlService = $container->get(UrlTheseService::class);

        $service = new UrlTheseHelper();
        $service->setUrlTheseService($urlService);

        return $service;
    }
}
