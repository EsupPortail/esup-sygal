<?php

namespace Application\Controller;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Role;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
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

    public const PERIMETRE_ED = 'ED';
    public const PERIMETRE_UR = 'UR';
    public const PERIMETRE_Etab = 'Etab';
    public const PERIMETRE_These = 'These';
    public const PERIMETRE_Aucun = 'Aucun';

    public function indexAction()
    {
        $depend = $this->params()->fromQuery("depend");
        $categorie = $this->params()->fromQuery("categorie");
        $role = $this->params()->fromQuery("role");

        $qbRoles = $this->applicationRoleService->getRepository()->createQueryBuilder("r");
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

        $qbPrivileges = $this->privilegeService->getRepo()->createQueryBuilder("p");
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
        $qb = $this->applicationRoleService->getRepository()->createQueryBuilder("r");
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
        $qb = $this->applicationRoleService->getRepository()->createQueryBuilder('r')
            ->leftJoin('r.profils', 'p')->addSelect('p')
            ->where('r = :role')->setParameter('role', $this->params()->fromRoute('role'));
        try {
            /** @var Role $role */
            $role = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return new JsonModel([
                'success' => false,
                'error' => "Anomalie rencontrée : plusieurs rôles trouvés avec le même id !",
            ]);
        }

        if ($role->getProfil()) {
            return new JsonModel([
                'success' => false,
                'error' => "Seuls les rôles sans profil sont concernés !",
            ]);
        }

        $qb = $this->privilegeService->getRepo()->createQueryBuilder('p')
            ->leftJoin('p.roles', 'r')->addSelect('r')
            ->where('p = :privilege')->setParameter('privilege', $this->params()->fromRoute('privilege'));
        try {
            /** @var Privilege $privilege */
            $privilege = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return new JsonModel([
                'success' => false,
                'error' => "Anomalie rencontrée : plusieurs privilèges trouvés avec le même id !",
            ]);
        }

        try {
            // NB : PrivilegeService déclenchera un événement permettant de vider le cache des rôles par privilèges.
            $value = (int) $this->privilegeService->toggle($role, $privilege);
        } catch (Exception $e) {
            return new JsonModel([
                'success' => false,
                'error' => "Un problème est survenu lors de l'ajout/retrait du privilège : " . $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }

        return new JsonModel([
            'success' => true,
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