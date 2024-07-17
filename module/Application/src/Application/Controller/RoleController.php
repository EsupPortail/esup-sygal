<?php

namespace Application\Controller;

use Application\Entity\Db\Role;
use Application\Form\RoleFormAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Laminas\View\Model\ViewModel;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class RoleController  extends AbstractController {
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use RoleFormAwareTrait;
    use SourceAwareTrait;
    use \UnicaenAuth\Service\Traits\RoleServiceAwareTrait;

    public function indexAction()
    {
        $etablissementsMembres = $this->getEtablissementService()->getRepository()->findAllEtablissementsMembres();

        $mappedRoles = [];
        foreach ($etablissementsMembres as $etablissement) {
            $roles = $this->getRoleService()->getRepository()->findAllRolesTheseDependantForStructureConcrete($etablissement);
            $mappedRoles[$etablissement->getStructure()->getCode()] = $roles;
        }
        return new ViewModel([
            'etablissements' => $etablissementsMembres,
            'mappedRoles' => $mappedRoles,
        ]);
    }

    public function ajouterAction()
    {
        $request = $this->getRequest();
        $form = $this->getRoleForm();



        $viewModel = new ViewModel([
            'form' => $form,
        ]);

        $form->bind(new Role());

        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = $request->getPost();
        $form->setData($data);
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var Role $role */
        $role = $form->getData();
        $role->setSource($this->source);
        $role->setSourceCode(uniqid());

//        $this->theseService->saveThese($these);

        $this->flashMessenger()->addSuccessMessage("Rôle créé avec succès.");

//        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
        return null;
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