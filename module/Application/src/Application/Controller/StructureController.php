<?php

namespace Application\Controller;

use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Zend\View\Model\ViewModel;

class StructureController extends AbstractController {
    use RoleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use StructureServiceAwareTrait;
    use NotificationServiceAwareTrait;

    public function individuRoleAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $structure = $this->structureService->findStructureById($structureId);
        $type = $this->params()->fromRoute("type");

//        var_dump($structure->getLibelle());

        $roles_tmp = $this->roleService->getRolesByStructure($structure);
        $roles = [];
        /** @var Role $role */
        foreach ($roles_tmp as $role) {
            if (!$role->isTheseDependant()) $roles[] = $role;
        }

        $individuRoles = $this->roleService->getIndividuRoleByStructure($structure);

        $repartition = [];
        foreach ($roles as $role) {
            $repartition[$role->getId()] = [];
        }

        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            $role = $individuRole->getRole();
            $individu = $individuRole->getIndividu();
            $repartition[$role->getId()][] = $individu;
        }

        $membres = [];
        foreach ($repartition as $role => $individus) {
            $membres = array_merge($membres, $individus);
        }
        $membres = array_unique($membres);

        return new ViewModel([
            'roles' => $roles,
            'membres' => $membres,
            'repartition' => $repartition,
            'type' => $type,
        ]);
    }
}