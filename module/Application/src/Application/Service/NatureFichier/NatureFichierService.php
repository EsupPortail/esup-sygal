<?php

namespace Application\Service\NatureFichier;

use Application\Entity\Db\NatureFichier;
use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;

class NatureFichierService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(NatureFichier::class);
    }
}