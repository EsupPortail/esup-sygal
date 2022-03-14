<?php

namespace Application\Controller;

use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Laminas\View\Model\ViewModel;

class RoleController  extends AbstractController {
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    public function indexAction()
    {
        $etablissementsMembres = $this->getEtablissementService()->getRepository()->findAllEtablissementsMembres();

        $mappedRoles = [];
        foreach ($etablissementsMembres as $etablissement) {
            $roles = $this->getRoleService()->getRepository()->findAllRolesTheseDependantByEtablissement($etablissement);
            $mappedRoles[$etablissement->getStructure()->getCode()] = $roles;
        }
        return new ViewModel([
            'etablissements' => $etablissementsMembres,
            'mappedRoles' => $mappedRoles,
        ]);
    }

//    public function incrementerOrdreAction() {
//        $idRole = $this->params()->fromRoute('role');
//        $role = $this->getRoleService()->getRepository()->find($idRole);
//        $this->getRoleService()->incrementerOrdre($role);
//
//        $this->redirect()->toRoute('role-ordre');
//    }
//
//    public function decrementerOrdreAction() {
//        $idRole = $this->params()->fromRoute('role');
//        $role = $this->getRoleService()->getRepository()->find($idRole);
//        $this->getRoleService()->decrementerOrdre($role);
//
//        $this->redirect()->toRoute('role-ordre');
//    }
}