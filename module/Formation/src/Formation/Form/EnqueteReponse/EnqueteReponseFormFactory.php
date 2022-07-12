<?php

namespace Formation\Form\EnqueteReponse;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class EnqueteReponseFormFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var EnqueteReponseHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(EnqueteReponseHydrator::class);

        $form = new EnqueteReponseForm();
        $form->setEntityManager($entityManager);
        $form->setHydrator($hydrator);
        return $form;
    }
}