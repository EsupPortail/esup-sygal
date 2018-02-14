<?php

namespace Application\View\Helper\Url;

use Application\Service\Url\UrlTheseService;
use Zend\View\HelperPluginManager;

class UrlTheseHelperFactory
{
    public function __invoke(HelperPluginManager $helperPluginManager)
    {
        /** @var UrlTheseService $urlService */
        $urlService = $helperPluginManager->getServiceLocator()->get(UrlTheseService::class);

//        var_dump($urlService);die;

        $service = new UrlTheseHelper();
        $service->setUrlTheseService($urlService);

        return $service;
    }
}
