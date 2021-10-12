<?php

namespace Application\Controller;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Role;
use Application\Form\ProfilForm;
use Application\Service\Profil\ProfilServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Service\Traits\PrivilegeServiceAwareTrait;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class ProfilController extends AbstractActionController {
    use PrivilegeServiceAwareTrait;
    use ProfilServiceAwareTrait;
    use RoleServiceAwareTrait;

    /**
     * @var ProfilForm
     */
    private $profilForm;

    /**
     * @param ProfilForm $profilForm
     */
    public function setProfilForm(ProfilForm $profilForm)
    {
        $this->profilForm = $profilForm;
    }

    public function indexAction()
    {
        $profils = $this->getProfilService()->getProfils();
        $privileges = $this->getServicePrivilege()->getRepo()->findBy([], ['categorie' => 'ASC', 'ordre' => 'ASC']);

        return new ViewModel([
            'profils' => $profils,
            'privileges' => $privileges,
        ]);
    }

    public function editerAction() {
        /** @var Profil $profil */
        $profilId = $this->params()->fromRoute('profil');

        $profil = null;
        if($profilId)   $profil = $this->getProfilService()->getProfil($profilId);
        else            $profil = new Profil();

        /** @var ProfilForm $form */
        $form = $this->profilForm;
        $form->setAttribute('action', $this->url()->fromRoute('profil/editer', ['profil' => $profil->getId()], [], true));
        $form->bind($profil);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($profilId)  $this->getProfilService()->update($profil);
                else            $this->getProfilService()->create($profil);
                $this->redirect()->toRoute('profil', [], [], true);
            }
        }

        return new ViewModel([
            'title' => 'Édition d\'un profil',
            'form' => $form,
        ]);
    }

    public function supprimerAction() {
        /** @var Profil $profil */
        $profilId = $this->params()->fromRoute('profil');
        $profil = $this->getProfilService()->getProfil($profilId);

        if ($profil) {
            $this->getProfilService()->delete($profil);
        }

        $this->redirect()->toRoute('profil', [], [], true);

    }

    public function modifierProfilPrivilegeAction()
    {
        $privilegeId = $this->params()->fromRoute("privilege");
        $profilId = $this->params()->fromRoute("profil");

        /** @var Profil $profil */
        $profil = $this->getProfilService()->getProfil($profilId);
        /** @var Privilege $privilege */
        $privilege = $this->getServicePrivilege()->getRepo()->find($privilegeId);

        $value = null;
        if( $profil->hasPrivilege($privilege)) {
            $privilege->removeProfil($profil);
            $value = 0;
        } else {
            $privilege->addProfil($profil);
            $value = 1;
        }
        try {
            $this->getServicePrivilege()->getEntityManager()->flush($privilege);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème lors du changement de rôle s'est produit", $e);
        }
        // Application aux rôles associés au profil
        $this->getRoleService()->applyChangement($profil->getRoles(), $privilege, $value);

        return new JsonModel([
            'value' => $value,
        ]);
    }

    public function gererRolesAction()
    {
        /** @var Profil $profil */
        $profilId   = $this->params()->fromRoute('profil');
        $profil     = $this->getProfilService()->getProfil($profilId);

        $roles      = $this->getRoleService()->getRolesSansProfil();

        return new ViewModel([
            'profil' => $profil,
            'rolesDisponibles' => $roles,
        ]);
    }

    public function ajouterRoleAction()
    {
        /** @var Profil $profil */
        $profilId = $this->params()->fromRoute('profil');
        $profil = $this->getProfilService()->getProfil($profilId);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            /** @var Role $role */
            $roleId = $data['role'];
            $role = $this->getRoleService()->getRepository()->find($roleId);

            if (! $profil->hasRole($role)) {
                $profil->addRole($role);
                $this->getProfilService()->update($profil);
                $this->getProfilService()->applyProfilToRole($profil, $role);
            }
        }

        $this->redirect()->toRoute('profil/gerer-roles', ['profil' => $profil->getId()], [], true);
    }

    public function retirerRoleAction()
    {
        /** @var Profil $profil */
        $profilId = $this->params()->fromRoute('profil');
        $profil = $this->getProfilService()->getProfil($profilId);

        /** @var Role $role */
        $roleId = $this->params()->fromRoute('role');
        $role = $this->getRoleService()->getRepository()->find($roleId);

        if ($profil->hasRole($role)) {
            $profil->removeRole($role);
            $this->getProfilService()->update($profil);
        }

        $this->redirect()->toRoute('profil/gerer-roles', ['profil' => $profil->getId()], [], true);
    }

    public function dupliquerPrivilegesAction()
    {
        /** @var Profil $profil */
        $profilId = $this->params()->fromRoute('profil');
        $profil = $this->getProfilService()->getProfil($profilId);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $profilFrom = $this->getProfilService()->getProfil($data['profil']);

            $this->getProfilService()->copyPrivilegeFrom($profilFrom, $profil);
            $this->redirect()->toRoute('profil', [], [], true);
        }

        $profils = $this->getProfilService()->getProfils();
        return new ViewModel([
            'title' => 'Sélectionner un profil pour dupliquer les privilèges',
            'profil' => $profil,
            'profils' => $profils,
        ]);
    }
}