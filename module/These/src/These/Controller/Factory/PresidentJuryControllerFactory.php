<?php

namespace These\Controller\Factory;

use Application\Form\AdresseMail\AdresseMailForm;
use Depot\Service\These\DepotService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use These\Controller\PresidentJuryController;
use Acteur\Service\ActeurThese\ActeurTheseService;
use These\Service\These\TheseService;

class PresidentJuryControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PresidentJuryController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurTheseService $acteurService
         * @var MembreService $membreService
         * @var TheseService $theseService
         */
        $acteurService = $container->get(ActeurTheseService::class);
        $membreService = $container->get(MembreService::class);
        $theseService = $container->get(TheseService::class);

        /**
         * @var AdresseMailForm $adresseMailForm
         */
        $adresseMailForm = $container->get('FormElementManager')->get(AdresseMailForm::class);

        $controller = new PresidentJuryController();
        $controller->setActeurTheseService($acteurService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setAdresseMailForm($adresseMailForm);

        /** @var DepotService $depotService */
        $depotService = $container->get(DepotService::class);
        $controller->setDepotService($depotService);

        return $controller;
    }
}