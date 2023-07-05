<?php

namespace InscriptionAdministrative\Service;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\BaseService;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;

class InscriptionAdministrativeService extends BaseService
{
    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(InscriptionAdministrative::class);

        return $repo;
    }


}