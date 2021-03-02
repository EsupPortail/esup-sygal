<?php

namespace ComiteSuivi\Controller;

use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviForm;
use ComiteSuivi\Form\ComiteSuivi\RefusForm;
use ComiteSuivi\Form\CompteRendu\CompteRenduForm;
use ComiteSuivi\Form\Membre\MembreForm;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use ComiteSuivi\Service\CompteRendu\CompteRenduService;
use ComiteSuivi\Service\Membre\MembreService;
use ComiteSuivi\Service\Notifier\NotifierService;
use Interop\Container\ContainerInterface;

class ComiteSuiviControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return ComiteSuiviController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ComiteSuiviService $comiteSuiviService
         * @var CompteRenduService $compteRenduService
         * @var IndividuService $individuService
         * @var MembreService $membreService
         * @var NotifierService $notifierService
         * @var RoleService $roleService
         * @var TheseService $theseService
         * @var ValidationService $validationService
         */
        $comiteSuiviService = $container->get(ComiteSuiviService::class);
        $compteRenduService = $container->get(CompteRenduService::class);
        $individuService = $container->get('IndividuService');
        $membreService = $container->get(MembreService::class);
        $notifierService = $container->get(NotifierService::class);
        $roleService = $container->get('RoleService');
        $theseService = $container->get('TheseService');
        $validationService = $container->get('ValidationService');

        /**
         * @var ComiteSuiviForm $comiteSuiviForm
         * @var CompteRenduForm $compteRenduForm
         * @var MembreForm $membreForm
         * @var RefusForm $refusForm
         */
        $comiteSuiviForm = $container->get('FormElementManager')->get(ComiteSuiviForm::class);
        $compteRenduForm = $container->get('FormElementManager')->get(CompteRenduForm::class);
        $membreForm = $container->get('FormElementManager')->get(MembreForm::class);
        $refusForm = $container->get('FormElementManager')->get(RefusForm::class);

        $controller = new ComiteSuiviController();

        $controller->setComiteSuiviService($comiteSuiviService);
        $controller->setCompteRenduService($compteRenduService);
        $controller->setIndividuService($individuService);
        $controller->setMembreService($membreService);
        $controller->setNotifierService($notifierService);
        $controller->setRoleService($roleService);
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);

        $controller->setComiteSuiviForm($comiteSuiviForm);
        $controller->setCompteRenduForm($compteRenduForm);
        $controller->setMembreForm($membreForm);
        $controller->setRefusForm($refusForm);

        return $controller;
    }


}