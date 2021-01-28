<?php

namespace Application\Controller\Factory;

use Application\Controller\PresidentJuryController;
use Application\Form\AdresseMail\AdresseMailForm;
use Application\Service\Acteur\ActeurService;
use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;

class PresidentJuryControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PresidentJuryController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurService $acteurService
         * @var MembreService $membreService
         * @var TheseService $theseService
         */
        $acteurService = $container->get(ActeurService::class);
        $membreService = $container->get(MembreService::class);
        $theseService = $container->get(TheseService::class);

        /**
         * @var AdresseMailForm $adresseMailForm
         */
        $adresseMailForm = $container->get('FormElementManager')->get(AdresseMailForm::class);

        $controller = new PresidentJuryController();
        $controller->setActeurService($acteurService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setAdresseMailForm($adresseMailForm);
        return $controller;
    }
}