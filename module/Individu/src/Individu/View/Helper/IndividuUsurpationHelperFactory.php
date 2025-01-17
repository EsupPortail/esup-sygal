<?php

namespace Individu\View\Helper;

use Application\Service\Url\UrlService;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Psr\Container\ContainerInterface;
use UnicaenAuthentification\Options\ModuleOptions;
use UnicaenAuthentification\Service\UserContext;

class IndividuUsurpationHelperFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuUsurpationHelper
    {
        /** @var UrlService $urlService */
        $urlService = $container->get(UrlService::class);
        $url = $urlService->fromRoute('utilisateur/default', ['action' => 'usurper-individu']);

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
            'class' => 'individu-usurpation-submit btn btn-danger btn-sm',
            'title' => "Usurper" /*. " l'identité de " . $individu*/,
        ]);

        $form->add($hidden);
        $form->add($submit);

        return $form;
    }
}