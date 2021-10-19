<?php

namespace Soutenance\Service\Evenement;

use DateTime;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Soutenance\Entity\Evenement;
use Soutenance\Entity\Proposition;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class EvenementService {
    use EntityManagerAwareTrait;

    /** REQUETAGE *****************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(Evenement::class)->createQueryBuilder('evenement')
        ;
        return $qb;
    }

    /**
     * @param Proposition $proposition
     * @return Evenement[]
     */
    public function getEvenementsByProposition(Proposition $proposition) : array {
        $qb = $this->createQueryBuilder()
            ->andWhere('evenement.proposition = :proposition')
            ->setParameter('proposition', $proposition)
            ->orderBy('evenement.date', 'DESC')
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Proposition $proposition
     * @param int $type
     * @return Evenement[]
     */
    public function getEvenementsByPropositionAndType(Proposition $proposition, int $type) : array {
        $qb = $this->createQueryBuilder()
            ->andWhere('evenement.proposition = :proposition')
            ->setParameter('proposition', $proposition)
            ->andWhere('evenement.type = :type')
            ->setParameter('type', $type)
            ->orderBy('evenement.date', 'DESC')
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /** FACADE ********************************************************************************************************/

    public function ajouterEvenement(Proposition $proposition, int $type) : Evenement
    {
        $evenement = new Evenement();
        $evenement->setProposition($proposition);
        $evenement->setType($type);
        $evenement->setDate(new DateTime());

        try {
            $this->getEntityManager()->persist($evenement);
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'ajout de l'evenement");
        }
        return $evenement;
    }
}