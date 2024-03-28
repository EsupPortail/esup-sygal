<?php

namespace Individu\Service\IndividuRoleEtablissement;

use Application\Service\BaseService;
use Individu\Entity\Db\IndividuRole;
use Individu\Entity\Db\Repository\IndividuRoleRepository;

class IndividuRoleEtablissementService extends BaseService
{
    public function getRepository(): IndividuRoleRepository
    {
        /** @var \Individu\Entity\Db\Repository\IndividuRoleRepository $repo */
        $repo = $this->entityManager->getRepository(IndividuRole::class);

        return $repo;
    }


}