<?php

namespace Application\Controller\Factory;

use Application\Controller\DoctorantController;
use Application\Form\MailConfirmationForm;
use Application\Service\Doctorant\DoctorantService;
use Application\Service\MailConfirmationService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Authentication\Adapter\Ldap as LdapAuthAdapter;

class DoctorantControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return DoctorantController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var VariableService $variableService
         * @var DoctorantService $doctorantService
         * @var MailConfirmationService $mailConfirmationService
         */
        $variableService = $container->get('VariableService');
        $doctorantService = $container->get('DoctorantService');
        $mailConfirmationService = $container->get('MailConfirmationService');

        /** @var MailConfirmationForm $mailConfirmationForm */
        $mailConfirmationForm = $container->get('FormElementManager')->get('MailConfirmationForm');

        /** @var LdapAuthAdapter $authAdapter */
        $ldapAuthAdapter = $container->get('UnicaenAuth\Authentication\Adapter\Ldap');

        $controller = new DoctorantController();
        $controller->setVariableService($variableService);
        $controller->setDoctorantService($doctorantService);
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setMailConfirmationForm($mailConfirmationForm);
        $controller->setLdapAuthAdapter($ldapAuthAdapter);

        return $controller;
    }
}
