<?php

namespace Formation\Controller;

use Application\Service\Doctorant\DoctorantService;
use Application\Service\Individu\IndividuService;
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
         * @var DoctorantService $doctorantService
         * @var InscriptionService $inscriptionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $doctorantService = $container->get(DoctorantService::class);
        $individuService = $container->get(IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);


        $controller = new InscriptionController();
        /** services **************************************************************************************************/
        $controller->setEntityManager($entityManager);
        $controller->setDoctorantService($doctorantService);
        $controller->setIndividuService($individuService);
        $controller->setInscriptionService($inscriptionService);
        /** forms *****************************************************************************************************/

        return $controller;
    }
}