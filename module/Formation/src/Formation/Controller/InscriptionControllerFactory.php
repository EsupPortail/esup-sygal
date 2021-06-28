<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Service\Inscription\InscriptionService;
use Interop\Container\ContainerInterface;

class InscriptionControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return InscriptionController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var InscriptionService $seanceService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $seanceService = $container->get(InscriptionService::class);


        $controller = new InscriptionController();
        /** services **************************************************************************************************/
        $controller->setEntityManager($entityManager);
        $controller->setInscriptionService($seanceService);
        /** forms *****************************************************************************************************/

        return $controller;
    }
}