<?php

namespace Application\Controller\Plugin\Url;

use Application\Service\Url\UrlTheseService;
use Zend\Mvc\Controller\PluginManager;

class UrlThesePluginFactory
{
    public function __invoke(PluginManager $helperPluginManager)
    {
        /** @var UrlTheseService $urlService */
        $urlService = $helperPluginManager->getServiceLocator()->get(UrlTheseService::class);

        $service = new UrlThesePlugin();
        $service->setUrlTheseService($urlService);

        return $service;
    }
}
