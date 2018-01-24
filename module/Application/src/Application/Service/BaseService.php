<?php

namespace Application\Service;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\EntityManager;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;

abstract class BaseService implements EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;
    use MessageAwareTrait;

    /**
     * @return DefaultEntityRepository
     */
    abstract public function getRepository();

    /**
     * Proxy method.
     *
     * @see EntityManager::beginTransaction()
     */
    public function beginTransaction()
    {
        $this->getEntityManager()->beginTransaction();
    }

    /**
     * Proxy method.
     *
     * @see EntityManager::commit()
     */
    public function commit()
    {
        $this->getEntityManager()->commit();
    }

    /**
     * Proxy method.
     *
     * @see EntityManager::rollback()
     */
    public function rollback()
    {
        $this->getEntityManager()->rollback();
    }
}