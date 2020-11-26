<?php

namespace Application\Service\CoEncadrant;

use Application\Entity\Db\Acteur;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class CoEncadrantService {
    use EntityManagerAwareTrait;

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() {
        $qb = $this->getEntityManager()->getRepository(Acteur::class)->createQueryBuilder('acteur')
            ->addSelect('individu')->join('acteur.individu', 'individu')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('role.code = :code')
            ->setParameter('code', 'B')
        ;
        return $qb;
    }

    /**
     * @param string $term
     * @return Acteur[]
     */
    public function findByText(string $term)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("LOWER(concat(concat(concat(concat(individu.prenom1, ' '),individu.nomUsuel), ' '), individu.prenom1)) like :term")
            ->setParameter('term', '%'.strtolower($term).'%')
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param int $id
     * @return Acteur
     */
    public function getCoEncadrant(int $id)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('acteur.id = :id')
            ->setParameter('id', $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Acteur partagent le même id [".$id."].");
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Acteur
     */
    public function getRequestedCoEncadrant(AbstractActionController $controller, string $param = 'co-encadrant')
    {
        $id = $controller->params()->fromRoute($param);
        $result = $this->getCoEncadrant($id);
        return $result;
    }
}