<?php

namespace Soutenance\Form\Membre;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;


class MembreFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var QualiteService $qualiteService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $qualiteService = $container->get(QualiteService::class);

        /** @var MembreHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(MembreHydrator::class);

        /** @var MembreForm $form */
        $form = new MembreForm();
        $form->setEntityManager($entityManager);
        $form->setQualiteService($qualiteService);
        $form->setHydrator($hydrator);

        return $form;
    }
}