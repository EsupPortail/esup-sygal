<?php

namespace InscriptionAdministrative\Service;

use Application\Service\BaseService;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\Repository\InscriptionAdministrativeRepository;

class InscriptionAdministrativeService extends BaseService
{
    public function getRepository(): InscriptionAdministrativeRepository
    {
        /** @var InscriptionAdministrativeRepository $repo */
        $repo = $this->entityManager->getRepository(InscriptionAdministrative::class);

        return $repo;
    }
}