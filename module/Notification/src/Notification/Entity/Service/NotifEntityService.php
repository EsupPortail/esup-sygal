<?php

namespace Notification\Entity\Service;

use Notification\Entity\NotifEntity;
use Notification\Entity\Repository\NotifEntityRepository;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class NotifEntityService
{
    use EntityManagerAwareTrait;
    use MessageCollectorAwareTrait;

    /**
     * @return NotifEntityRepository
     */
    public function getRepository()
    {
        /** @var NotifEntityRepository $repository */
        $repository = $this->getEntityManager()->getRepository(NotifEntity::class);

        return $repository;
    }
}