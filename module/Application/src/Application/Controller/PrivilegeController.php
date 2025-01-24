<?php

namespace Application\Controller;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Role;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenPrivilege\Service\Privilege\PrivilegeCategorieServiceAwareTrait;
use UnicaenPrivilege\Service\Privilege\PrivilegeServiceAwareTrait;

class PrivilegeController extends AbstractController
{
    use PrivilegeCategorieServiceAwareTrait;
    use EntityManagerAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use PrivilegeServiceAwareTrait;

    const PERIMETRE_ED = 'ED';
    const PERIMETRE_UR = 'UR';
    const PERIMETRE_Etab = 'Etab';
    const PERIMETRE_These = 'These';
    const PERIMETRE_Aucun = 'Aucun';

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
            ->orderBy("r.typeStructureDependant, r.ordreAffichage, r.structure", 'asc');
        $this->applyFilterDependance($qbRoles, $depend);
        $this->applyFilterRole($qbRoles, $role);
        /** @var Role[] $roles */
        $roles = $qbRoles->getQuery()->execute();

        $qbPrivileges = $this->entityManager->getRepository(Privilege::class)->createQueryBuilder("p");
        $qbPrivileges
            ->addSelect('r')
            ->leftJoin('p.roles', 'r')
            ->orderBy("p.categorie, p.ordre", "ASC");
        $this->applyFilterCategorie($qbPrivileges, $categorie);
        /** @var Privilege[] $privileges */
        $privileges = $qbPrivileges->getQuery()->execute();

        return new ViewModel([
            'roles'          => $roles,
            'privileges'     => $privileges,
            'categoriesPrivilegesForFilter' => $this->fetchCategoriesPrivilegesForFilter(),
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

    private function fetchCategoriesPrivilegesForFilter(): array
    {
        return $this->privilegeCategorieService->findAll(['libelle' => 'asc']);
    }

    /**
     * Ajout ou retrait d'un privilège à un rôle.
     */
    public function modifierAction(): JsonModel
    {
        /** @var Role $role */
        $role = $this->entityManager->getRepository(Role::class)->find($this->params()->fromRoute("role"));
        if ($role->getProfil()) {
            throw new RuntimeException("Seuls les rôles sans profil sont concernés");
        }

        /** @var Privilege $privilege */
        $privilege = $this->entityManager->getRepository(Privilege::class)->find($this->params()->fromRoute("privilege"));

        if (in_array($role, $privilege->getRoles()->toArray())) {
            $privilege->removeRole($role);
            $value = 0;
        } else {
            $privilege->addRole($role);
            $value = 1;
        }
        try {
            $this->getEntityManager()->flush($privilege);
        } catch (ORMException $e) {
            throw new RuntimeException(
                sprintf("Un problème est survenu lors de %s du privilège.", $value ? "l'ajout" : "la suppression"),
                $e
            );
        }

        return new JsonModel([
            'value' => $value,
        ]);
    }

    private function applyFilterDependance(QueryBuilder $qb, $depend): QueryBuilder
    {
        switch ($depend) {
            case self::PERIMETRE_ED:
                $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "2");
                break;
            case self::PERIMETRE_UR:
                $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "3");
                break;
            case self::PERIMETRE_Etab:
                $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "1");
                break;
            case self::PERIMETRE_These:
                $qb->andWhere("r.theseDependant = :value")
                    ->setParameter("value", true);
                break;
            case self::PERIMETRE_Aucun:
                $qb->andWhere("r.theseDependant = :value")
                    ->andWhere("r.typeStructureDependant IS NULL")
                    ->setParameter("value", false);
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

    private function applyFilterRole(QueryBuilder $qb, $role): QueryBuilder
    {
        if ($role !== null && $role !== "") {
            $qb
                ->andWhere("r.libelle = :role")
                ->setParameter("role", $role);
        }

        return $qb;
    }
}