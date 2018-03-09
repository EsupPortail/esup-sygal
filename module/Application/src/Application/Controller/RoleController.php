<?php

namespace Application\Controller;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareInterface;
use Application\Service\Role\RoleServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Db\CategoriePrivilege;
use Zend\View\Model\ViewModel;

class RoleController extends AbstractController
        implements EntityManagerAwareInterface, RoleServiceAwareInterface
{
    use EntityManagerAwareTrait;
    use RoleServiceAwareTrait;

    public function indexAction()
    {
        $depend = $this->params()->fromQuery("depend");
        $categorie = $this->params()->fromQuery("categorie");

        $qb_depend = $this->entityManager->getRepository(Role::class)->createQueryBuilder("r");
        $qb_depend = $this->decorateWithDepend($qb_depend, $depend);
        $roles = $qb_depend->getQuery()->execute();
        $qb_categorie = $this->entityManager->getRepository(Privilege::class)->createQueryBuilder("p");
        $qb_categorie = $this->decorateWithCategorie($qb_categorie, $categorie);
        $privileges = $qb_categorie->getQuery()->execute();
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
//        var_dump($privilege_id);
//        var_dump($role_id);
        $privilege = $this->entityManager->getRepository(Privilege::class)->findOneBy(["id" => $privilege_id]);
        $role = $this->entityManager->getRepository(Role::class)->findOneBy(["id" => $role_id]);

        if( array_search($role, $privilege->getRole()->toArray()) !== false) {
            $privilege->removeRole($role);
            $this->entityManager->flush($privilege);
        } else {
            $privilege->addRole($role);
            $this->entityManager->flush($privilege);
        }

//        var_dump($privilege);
        //$has = array_search($role_id, $privilege->getRoles());
        //var_dump($has);
        //die("Die die");
        $queryParams = $this->params()->fromQuery();
        $this->redirect()->toRoute("roles", [], ["query" => $queryParams], true);
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
        if ($categorie !== null && $categorie !== "") {
            $qb = $qb->leftJoin(CategoriePrivilege::class, "cp", "WITH", "cp.id = p.categorie")
                ->andWhere("cp.code = :type")
                ->setParameter("type", $categorie);
        }
        return $qb;
    }
}