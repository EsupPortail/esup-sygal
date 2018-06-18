<?php

namespace Application\Controller;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Role;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Db\CategoriePrivilege;
use Zend\View\Model\ViewModel;

class RoleController extends AbstractController
{
    use EntityManagerAwareTrait;
    use RoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    public function indexAction()
    {
        $depend = $this->params()->fromQuery("depend");
        $categorie = $this->params()->fromQuery("categorie");

        $qb_depend = $this->entityManager->getRepository(Role::class)->createQueryBuilder("r");
        $qb_depend = $this->decorateWithDepend($qb_depend, $depend);
        $qb_depend = $qb_depend->orderBy("r.typeStructureDependant, r.libelle, r.structure", 'asc');
        $roles = $qb_depend->getQuery()->execute();
        $qb_categorie = $this->entityManager->getRepository(Privilege::class)->createQueryBuilder("p");
        $qb_categorie = $this->decorateWithCategorie($qb_categorie, $categorie);
        $qb_categorie->orderBy("p.categorie, p.ordre","ASC");
        $privileges = $qb_categorie->getQuery()->execute();

        $substituees = $this->getStructureService()->getStructuresSubstituees();

        //Retrait des rôles associés à des structures historisées ou substituées
        $roles = array_filter($roles, function (Role $role) use ($substituees) {
            $structure = $role->getStructure();
            if (array_search($structure, $substituees))  return false;
            if ($structure === null) return true;
            $structureConcrete = $this->getStructureService()->findStructureConcreteFromStructure($structure);
            if ($structureConcrete === null) return true;
            return  $structureConcrete->estNonHistorise();
        });

        return new ViewModel([
            'roles' => $roles,
            'privileges' => $privileges,
            'params' => $this->params()->fromQuery(),
        ]);
    }

    public function modifierAction()
    {
        $privilege_id = $this->params()->fromRoute("privilege");
        $role_id = $this->params()->fromRoute("role");
        $privilege = $this->entityManager->getRepository(Privilege::class)->findOneBy(["id" => $privilege_id]);
        $role = $this->entityManager->getRepository(Role::class)->findOneBy(["id" => $role_id]);


        $value = null;
        if( array_search($role, $privilege->getRole()->toArray()) !== false) {
            $privilege->removeRole($role);
            $this->entityManager->flush($privilege);
            $value = 0;
        } else {
            $privilege->addRole($role);
            $this->entityManager->flush($privilege);
            $value = 1;
        }

        $queryParams = $this->params()->fromQuery();
        return new ViewModel([
            'value' => $value,
        ]);
        //$this->redirect()->toRoute("roles", [], ["query" => $queryParams], true);
    }

    private function decorateWithDepend(QueryBuilder $qb, $depend) {
        switch($depend) {
            case "ED" :
                $qb = $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "2");
                return $qb;
            case "UR" :
                $qb = $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "3");
                return $qb;
            case "Etab" :
                $qb = $qb->andWhere("r.typeStructureDependant = :type")
                    ->setParameter("type", "1");
                return $qb;
            case "These" :
                $qb = $qb->andWhere("r.theseDependant = :value")
                    ->setParameter("value", true);
                return $qb;
            case "Aucune" :
                $qb = $qb->andWhere("r.theseDependant = :value")
                    ->andWhere("r.typeStructureDependant IS NULL")
                    ->setParameter("value", false);
                return $qb;
            default:
                return $qb;
        }
    }

    private function decorateWithCategorie(QueryBuilder $qb, $categorie)
    {
        $qb->leftJoin(CategoriePrivilege::class, "cp", "WITH", "cp.id = p.categorie");
        if ($categorie !== null && $categorie !== "") {
            $qb
                ->andWhere("cp.code = :type")
                ->setParameter("type", $categorie);
        }
        return $qb;
    }
}