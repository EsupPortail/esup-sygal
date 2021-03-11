<?php

namespace Application\View\Helper;

use Interop\Container\ContainerInterface;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\UserContext;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;
use Zend\Form\Form;
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
        $helper->setForm($this->createForm($url));
        $helper->setUsurpationEnabled($usurpationAllowed);

        return $helper;
    }

    /**
     * @param string $url
     * @return Form
     */
    private function createForm(string $url)
    {
        $form = new Form('individu-usurpation-form');
        $form->setAttributes([
            'class'  => 'individu-usurpation-form',
            'action' => $url,
        ]);

        $hidden = null;
        $hidden = new Hidden('individu');
        $hidden->setAttributes([
            'id' => 'individu-usurpation-hidden',
        ]);

        $submit = new Submit('submit');
        $submit->setValue("Usurper");
        $submit->setAttributes([
            'class' => 'individu-usurpation-submit btn btn-danger btn-xs',
            'title' => "Usurper" /*. " l'identité de " . $individu*/,
        ]);

        $form->add($hidden);
        $form->add($submit);

        return $form;
    }
}