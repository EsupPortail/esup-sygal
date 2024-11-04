<?php

namespace Application\Controller;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Role;
use Application\Form\ProfilForm;
use Application\Service\Profil\ProfilServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Service\Traits\PrivilegeServiceAwareTrait;

class ProfilController extends AbstractActionController
{
    use PrivilegeServiceAwareTrait;
    use ProfilServiceAwareTrait;
    use RoleServiceAwareTrait;

    const PERIMETRE_ED = "ED";
    const PERIMETRE_UR = "UR";
    const PERIMETRE_Etab = "Etab";
    const PERIMETRE_Aucun = "Aucun";

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

    public function indexAction(): ViewModel
    {
        $depend = $this->params()->fromQuery("depend");
        $categorie = $this->params()->fromQuery("categorie");
        $profil = $this->params()->fromQuery("profil");
        
        $qbProfils = $this->profilService->getRepository()->createQueryBuilder("profil");
        $qbProfils
            ->addSelect('ts')->leftJoin('profil.structureType', 'ts')
            ->orderBy("profil.structureType, profil.libelle", 'asc');
        $this->applyFilterDependance($qbProfils, $depend);
        $this->applyFilterProfil($qbProfils, $profil);
        /** @var Profil[] $profils */
        $profils = $qbProfils->getQuery()->execute();

        $qbPrivileges = $this->getServicePrivilege()->getRepo()->createQueryBuilder("p");
        $qbPrivileges
            ->addSelect('profil')->leftJoin('p.profils', 'profil')
            ->orderBy("p.categorie, p.ordre", "ASC");
        $this->applyFilterCategorie($qbPrivileges, $categorie);
        /** @var Privilege[] $privileges */
        $privileges = $qbPrivileges->getQuery()->execute();

        return new ViewModel([
            'profils' => $profils,
            'privileges' => $privileges,
            'profilsForFilter' => $this->fetchProfilsForFilter($depend),
            'params' => $this->params()->fromQuery(),
        ]);
    }

    private function applyFilterDependance(QueryBuilder $qb, $depend): QueryBuilder
    {
        switch ($depend) {
            case self::PERIMETRE_ED:
                $qb->andWhere("profil.structureType = :type")->setParameter("type", "2");
                break;
            case self::PERIMETRE_UR:
                $qb->andWhere("profil.structureType = :type")->setParameter("type", "3");
                break;
            case self::PERIMETRE_Etab:
                $qb->andWhere("profil.structureType = :type")->setParameter("type", "1");
                break;
            case self::PERIMETRE_Aucun:
                $qb->andWhere("profil.structureType IS NULL");
                break;
            default:
                break;
        }

        return $qb;
    }

    private function applyFilterCategorie(QueryBuilder $qb, $categorie): QueryBuilder
    {
        $qb->leftJoin('p.categorie', "cp");
        if ($categorie !== null && $categorie !== "") {
            $qb
                ->andWhere("cp.code = :type")
                ->setParameter("type", $categorie);
        }

        return $qb;
    }

    private function applyFilterProfil(QueryBuilder $qb, $profil): QueryBuilder
    {
        if ($profil !== null && $profil !== "") {
            $qb
                ->andWhere("profil.libelle = :profil")
                ->setParameter("profil", $profil);
        }

        return $qb;
    }

    private function fetchProfilsForFilter($depend): array
    {
        // pour filtre par (libellé de) rôle
        $qb = $this->profilService->getRepository()->createQueryBuilder("profil");
        $qb
            ->orderBy('profil.libelle');

        $this->applyFilterDependance($qb, $depend);

        /** @var Profil[] $profils */
        $profils = $qb->getQuery()->getResult();

        return $profils;
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

    public function gererRolesAction(): ViewModel
    {
        /** @var Profil $profil */
        $profilId   = $this->params()->fromRoute('profil');
        $profil     = $this->getProfilService()->getProfil($profilId);

        $roles      = $this->getRoleService()->findRolesSansProfil();

        return new ViewModel([
            'profil' => $profil,
            'rolesDisponibles' => $roles,
        ]);
    }

    public function ajouterRoleAction(): Response
    {
        /** @var Profil $profil */
        $profilId = $this->params()->fromRoute('profil');
        $profil = $this->getProfilService()->getProfil($profilId);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            /** @var Role $role */
            $roleId = $data['role'] ?? null;
            if ($roleId) {
                $role = $this->getRoleService()->getRepository()->find($roleId);

                if (!$profil->hasRole($role)) {
                    $profil->addRole($role);
                    $this->getProfilService()->update($profil);
                    $this->getProfilService()->applyProfilToRole($profil, $role);
                }
            }
        }

        return $this->redirect()->toRoute('profil/gerer-roles', ['profil' => $profil->getId()], [], true);
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