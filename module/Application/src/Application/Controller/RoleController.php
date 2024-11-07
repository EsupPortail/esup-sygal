<?php

namespace Application\Controller;

use Application\Entity\Db\Role;
use Application\Form\RoleFormAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Laminas\View\Model\ViewModel;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class RoleController extends AbstractController
{
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use RoleFormAwareTrait;
    use SourceAwareTrait;
    use \UnicaenAuth\Service\Traits\RoleServiceAwareTrait;

    public function indexAction()
    {
        $etablissementsInscrs = $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions();

        $mappedRoles = [];
        foreach ($etablissementsInscrs as $etablissement) {
            $roles = $this->getRoleService()->getRepository()->findAllRolesTheseDependantForStructureConcrete($etablissement);
            $mappedRoles[$etablissement->getStructure()->getCode()] = $roles;
        }
        return new ViewModel([
            'etablissements' => $etablissementsInscrs,
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

        $this->flashMessenger()->addSuccessMessage("Rôle créé avec succès.");

        return null;
    }
}