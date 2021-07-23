<?php

namespace Formation\Service\EnqueteQuestion;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class EnqueteQuestionServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return EnqueteQuestionService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new EnqueteQuestionService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}