<?php

namespace Application\View\Helper;

use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\UserContext;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;

class IndividuUsurpationHelperFactory
{
    /**
     * @param HelperPluginManager $hpm
     * @return IndividuUsurpationHelper
     */
    public function __invoke(HelperPluginManager $hpm)
    {
        /** @var Url $urlHelper */
        $urlHelper = $hpm->get('url');
        $url = $urlHelper->__invoke('utilisateur/default', ['action' => 'usurper-individu']);

        /** @var UserContext $userContextService */
        $userContextService = $hpm->getServiceLocator()->get('authUserContext');

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $hpm->getServiceLocator()->get('unicaen-auth_module_options');

        $usurpationAllowed = in_array(
            $userContextService->getIdentityUsername(),
            $moduleOptions->getUsurpationAllowedUsernames());

        $helper = new IndividuUsurpationHelper($userContextService);
        $helper->setUrl($url);
        $helper->setUsurpationEnabled($usurpationAllowed);

        return $helper;
    }
}