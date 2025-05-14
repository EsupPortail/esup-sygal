<?php

namespace Doctorant\Controller;

use Application\Form\MailConfirmationForm;
use Application\Service\MailConfirmationService;
use Doctorant\Facade\TableauDeBordDoctorantFacade;
use Doctorant\Form\Doctorant\DoctorantForm;
use Doctorant\Form\MailConsentementForm;
use Doctorant\Service\DoctorantService;
use Interop\Container\ContainerInterface;

class DoctorantControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantController
    {
        $controller = new DoctorantController();

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get('DoctorantService');
        $controller->setDoctorantService($doctorantService);

        /** @var DoctorantForm $doctorantForm */
        $doctorantForm = $container->get('FormElementManager')->get(DoctorantForm::class);
        $controller->setDoctorantForm($doctorantForm);

        /** @var MailConfirmationService $mailConfirmationService */
        $mailConfirmationService = $container->get('MailConfirmationService');
        $controller->setMailConfirmationService($mailConfirmationService);

        /** @var MailConfirmationForm $mailConfirmationForm */
        $mailConfirmationForm = $container->get('FormElementManager')->get('MailConfirmationForm');
        $controller->setMailConfirmationForm($mailConfirmationForm);

        /** @var MailConsentementForm $mailConsentementForm */
        $mailConsentementForm = $container->get('FormElementManager')->get(MailConsentementForm::class);
        $controller->setMailConsentementForm($mailConsentementForm);

        /** @var TableauDeBordDoctorantFacade $tdbDoctorantFacade */
        $tdbDoctorantFacade = $container->get(TableauDeBordDoctorantFacade::class);
        $controller->setTableauDeBordDoctorantFacade($tdbDoctorantFacade);

        return $controller;
    }
}
