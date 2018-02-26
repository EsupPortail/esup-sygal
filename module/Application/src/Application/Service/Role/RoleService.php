<?php

namespace Application\Service\Role;

use Application\Entity\Db\Repository\RoleRepository;
use Application\Entity\Db\Role;
use Application\Service\BaseService;

class RoleService extends BaseService
{
    /**
     * @return RoleRepository
     */
    public function getRepository()
    {
        /** @var RoleRepository $repo */
        $repo = $this->entityManager->getRepository(Role::class);

        return $repo;
    }
}