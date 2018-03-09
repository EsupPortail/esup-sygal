<?php

namespace Application\Controller;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Role;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Db\CategoriePrivilege;
use Zend\View\Model\ViewModel;

class RoleController extends AbstractController
        implements EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

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
        ]);
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