<?php

namespace Formation\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;

class InscriptionRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Inscription|null
     */
    public function getRequestedInscription(AbstractActionController $controller, string $param = 'inscription') : ?Inscription
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Inscription|null $inscription */
        $inscription = $this->find($id);
        return $inscription;
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function createQB(string $alias) : QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias)
            ->join($alias.".session", "session")->addSelect("session")
            ->join("session.formation", "formation")->addSelect("formation")
            ->join($alias.".doctorant", "doctorant")->addSelect("doctorant")
        ;
        return $qb;
    }

    /**
     * @param array $filtres
     * @return Session[]
     */
    public function fetchInscriptionsWithFiltres(array $filtres) : array
    {
        $alias = 'inscription';
        $qb = $this->createQB($alias);

        if ($filtres['session']) {
            $libelle = '%' . strtolower($filtres['session']) . '%';
            $qb = $qb
                ->andWhere('lower(module.libelle) like :libelle')
                ->setParameter('libelle', $libelle);
        }
        if ($filtres['doctorant']) {
            $qb = $qb->leftJoin('doctorant.individu', 'individu')->addSelect('individu')
                ->andWhere("(lower(individu.nomUsuel)) = :doctorant")
                ->setParameter('doctorant', strtolower($filtres['doctorant']));
        }
        if ($filtres['liste']) {
            if ($filtres['liste'] === 'null') {
                $qb = $qb->andWhere($alias.'.liste IS NULL');
            } else {
                $qb = $qb->andWhere($alias . '.liste = :liste')
                    ->setParameter('liste', $filtres['liste']);
            }
        }

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Doctorant $doctorant
     * @return array
     */
    public function findInscriptionsByDoctorant(Doctorant $doctorant) : array
    {
        $qb = $this->createQB('inscription')
            ->andWhere('inscription.doctorant = :doctorant')
            ->setParameter('doctorant', $doctorant)
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}