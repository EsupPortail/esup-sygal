<?php

namespace Application\Service\Acteur;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Repository\ActeurRepository;
use Application\Service\BaseService;

class ActeurService extends BaseService
{
    /**
     * @return ActeurRepository
     */
    public function getRepository()
    {
        /** @var ActeurRepository $repo */
        $repo = $this->entityManager->getRepository(Acteur::class);

        return $repo;
    }
}