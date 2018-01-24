<?php

namespace Application\View\Helper;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LanguageSelectorHelperFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $config = $helperPluginManager->getServiceLocator()->get('config');

        return new LanguageSelectorHelper($config['languages']['language-list']);
    }

}
