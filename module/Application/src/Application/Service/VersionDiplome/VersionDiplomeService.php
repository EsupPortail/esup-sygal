<?php

namespace Application\Service\VersionDiplome;

use Application\Entity\Db\Repository\VersionDiplomeRepository;
use Application\Entity\Db\VersionDiplome;
use Application\Service\BaseService;
use UnicaenApp\Service\EntityManagerAwareTrait;

class VersionDiplomeService extends BaseService
{
    use EntityManagerAwareTrait;

    public function getRepository(): VersionDiplomeRepository
    {
        /** @var VersionDiplomeRepository $repo */
        $repo = $this->entityManager->getRepository(VersionDiplome::class);

        return $repo;
    }
}