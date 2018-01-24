<?php

namespace Application\Service\Role;

use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Application\Entity\Db\Repository\DefaultEntityRepository;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 26/04/16
 * Time: 09:07
 */
class RoleService extends BaseService
{
    /**
     * @return DefaultEntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Role::class);
    }
}