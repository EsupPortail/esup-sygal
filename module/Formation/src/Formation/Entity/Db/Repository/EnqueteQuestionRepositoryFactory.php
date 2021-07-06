<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\EnqueteQuestion;
use Interop\Container\ContainerInterface;

class EnqueteQuestionRepositoryFactory {
    /**
     * @param ContainerInterface $container
     * @return EnqueteQuestionRepository
     */
    public function __invoke(ContainerInterface $container) : EnqueteQuestionRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var EnqueteQuestionRepository $repository */
        $repository = $entitymanager->getRepository(EnqueteQuestion::class);
        return $repository;
    }
}