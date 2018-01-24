<?php

namespace Application\View\Helper;

use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHelper;

class QueryParams extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @return array
     */
    public function __invoke()
    {
        return $this->render();
    }

    /**
     * @return array
     */
    public function render()
    {
        /** @var \Zend\View\HelperPluginManager $pluginManager */
        $pluginManager = $this->getServiceLocator();
        $serviceManager = $pluginManager->getServiceLocator();

        /** @var Request $request */
        $request = $serviceManager->get('request');

        return $request->getUri()->getQueryAsArray();
    }
}