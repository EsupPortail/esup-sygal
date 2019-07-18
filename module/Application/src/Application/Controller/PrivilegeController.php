<?php

namespace Application\Controller;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Role;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Service\Traits\PrivilegeServiceAwareTrait;
use Zend\View\Model\ViewModel;

class PrivilegeController extends AbstractController
{
    use EntityManagerAwareTrait;
    use RoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use PrivilegeServiceAwareTrait;

    public function indexAction()
    {
        $depend = $this->params()->fromQuery("depend");
        $categorie = $this->params()->fromQuery("categorie");
        $role = $this->params()->fromQuery("role");

        $qbRoles = $this->entityManager->getRepository(Role::class)->createQueryBuilder("r");
        $qbRoles
            ->addSelect('p')
            ->leftJoin('r.profils', 'p')
            ->addSelect('s')
            ->leftJoin('r.structure', 's')
            ->addSelect('ts')
            ->leftJoin('s.typeStructure', 'ts')
            ->orderBy("r.typeStructureDependant, r.libelle, r.structure", 'asc');
        $this->applyFilterDependance($qbRoles, $depend);
        $this->applyFilterRole($qbRoles, $role);
        /** @var Role[] $roles */
        $roles = $qbRoles->getQuery()->execute();

        $qbPrivileges = $this->entityManager->getRepository(Privilege::class)->createQueryBuilder("p");
        $qbPrivileges
            ->addSelect('r')
            ->leftJoin('p.role', 'r')
            ->orderBy("p.categorie, p.ordre", "ASC");
        $this->applyFilterCategorie($qbPrivileges, $categorie);
        /** @var Privilege[] $privileges */
        $privileges = $qbPrivileges->getQuery()->execute();

        // Retrait des rôles associés à des structures historisées ou substituées
        $roles = $this->cleanRoles($roles);

        return new ViewModel([
            'roles'          => $roles,
            'privileges'     => $privileges,
            'rolesForFilter' => $this->fetchRolesForFilter($depend),
            'params'         => $this->params()->fromQuery(),
        ]);
    }

    private function fetchRolesForFilter($depend)
    {
        // pour filtre par (libellé de) rôle
        $qb = $this->entityManager->getRepository(Role::class)->createQueryBuilder("r");
        $qb
            ->orderBy('r.libelle');

        $this->applyFilterDependance($qb, $depend);

        /** @var Role[] $roles */
        $roles = $qb->getQuery()->getResult();

        return $roles;
    }

    /**
     * Retrait des rôles associés à des structures historisées ou substituées
     *
     * @param Role[] $roles
     * @return Role[]
     */
    private function cleanRoles($roles)
    {
        $substituees = $this->structureService->getStructuresSubstituees();

        // Retrait des rôles associés à des structures historisées ou substituées
        $roles = array_filter($roles, function (Role $role) use ($substituees) {
            $structure = $role->getStructure();
            if (array_search($structure, $substituees)) {
                return false;
            }
            if ($structure === null) {
                return true;
            }
            $structureConcrete = $this->structureService->findStructureConcreteFromStructure($structure);
            if ($structureConcrete === null) {
                return true;
            }

            return $structureConcrete->estNonHistorise();
        });

        return $roles;
    }

    public function modifierAction()
    {
        $privilege_id = $this->params()->fromRoute("privilege");
        $role_id = $this->params()->fromRoute("role");
        /**
         * @var Privilege $privilege
         * @var Role      $role
         */
        $privilege = $this->entityManager->getRepository(Privilege::class)->find($privilege_id);
        $role = $this->entityManager->getRepository(Role::class)->find($role_id);

        $value = null;

        // /!\ si le role à un privilège desactivé la modification
        if (!$role->getProfils()->isEmpty()) {
            if (array_search($role, $privilege->getRole()->toArray()) !== false) {
                $value = 1;
            } else {
                $value = 0;
            }
        } else {

            if (array_search($role, $privilege->getRole()->toArray()) !== false) {
                $privilege->removeRole($role);
                try {
                    $this->getEntityManager()->flush($privilege);
                } catch (OptimisticLockException $e) {
                    throw new RuntimeException("Un problème est survenu lors de la suppression du privilège.", $e);
                }
                $value = 0;
            } else {
                $privilege->addRole($role);
                try {
                    $this->getEntityManager()->flush($privilege);
                } catch (OptimisticLockException $e) {
                    throw new RuntimeException("Un problème est survenu lors de l'ajout du privilège.", $e);
                }
                $value = 1;
            }
            // retrait des profils associés à un role
            $this->getRoleService()->removeProfils($role);
        }

        return new ViewModel([
            'value' => $value,
        ]);
        //$this->redirect()->toRoute("roles", [], ["query" => $queryParams], true);
    }

    private function applyFilterDependance(QueryBuilder $qb, $depend)
    {
        switch ($depend) {
            case "ED" :
                $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "2");
                break;
            case "UR" :
                $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "3");
                break;
            case "Etab" :
                $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "1");
                break;
            case "These" :
                $qb->andWhere("r.theseDependant = :value")
                    ->setParameter("value", true);
                break;
            case "Aucune" :
                $qb->andWhere("r.theseDependant = :value")
                    ->andWhere("r.typeStructureDependant IS NULL")
                    ->setParameter("value", false);
                break;
            default:
                break;
        }

        return $qb;
    }

    private function applyFilterCategorie(QueryBuilder $qb, $categorie)
    {
        $qb->leftJoin('p.categorie', "cp");
        if ($categorie !== null && $categorie !== "") {
            $qb
                ->andWhere("cp.code = :type")
                ->setParameter("type", $categorie);
        }

        return $qb;
    }

    private function applyFilterRole(QueryBuilder $qb, $role)
    {
        if ($role !== null && $role !== "") {
            $qb
                ->andWhere("r.libelle = :role")
                ->setParameter("role", $role);
        }

        return $qb;
    }
}