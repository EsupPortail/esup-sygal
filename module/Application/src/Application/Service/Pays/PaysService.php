<?php

namespace Application\Service\Pays;

use Application\Entity\Db\Pays;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\BaseService;

/**
 * @method Pays|null findOneBy(array $criteria, array $orderBy = null)
 */
class PaysService extends BaseService
{
    /**
     * @return DefaultEntityRepository
     */
    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(Pays::class);

        return $repo;
    }

}