<?php

namespace Application\View\Helper;

use Interop\Container\ContainerInterface;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\UserContext;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;

class IndividuUsurpationHelperFactory
{
    /**
     * @param ContainerInterface $container
     * @return IndividuUsurpationHelper
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var HelperPluginManager $hpm */
        $hpm = $container->get('ViewHelperManager');

        /** @var Url $urlHelper */
        $urlHelper = $hpm->get('url');
        $url = $urlHelper->__invoke('utilisateur/default', ['action' => 'usurper-individu']);

        /** @var UserContext $userContextService */
        $userContextService = $container->get('authUserContext');

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('unicaen-auth_module_options');

        $usurpationAllowed = in_array(
            $userContextService->getIdentityUsername(),
            $moduleOptions->getUsurpationAllowedUsernames());

        $helper = new IndividuUsurpationHelper($userContextService);
        $helper->setUrl($url);
        $helper->setUsurpationEnabled($usurpationAllowed);

        return $helper;
    }
}