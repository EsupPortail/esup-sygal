<?php

namespace Individu\Form\IndividuRole;

use Application\Service\Role\RoleServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Individu\Entity\Db\IndividuRole;
use Individu\Service\IndividuServiceAwareTrait;

class IndividuRoleHydrator extends DoctrineObject
{
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;

    public function extract(object $object): array
    {
        /* @var IndividuRole $object */

        $data = parent::extract($object);

        $data['individu'] = $data['individu']->getNomComplet();
        $data['role'] = $data['role']->getLibelle();

        return $data;
    }

    public function hydrate(array $data, object $object): IndividuRole
    {
        /* @var IndividuRole $object */

        if ($data['individu']['id'] ?? null) {
            $data['individu'] = $this->individuService->getRepository()->find($data['individu']['id']);
        }
        if ($data['role']['id'] ?? null) {
            $data['role'] = $this->roleService->getRepository()->find($data['role']['id']);
        }

        return parent::hydrate($data, $object);
    }
}