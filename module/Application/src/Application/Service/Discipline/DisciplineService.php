<?php

namespace Application\Service\Discipline;

use Application\Entity\Db\Discipline;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class DisciplineService {
    use EntityManagerAwareTrait;

    /** REQUETAGE *****************************************************************************************************/

    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(Discipline::class)->createQueryBuilder('discipline');
        return $qb;
    }

    /**
     * @param string|null $code
     * @return Discipline|null
     */
    public function getDiscipline(?string $code) : ?Discipline
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('discipline.code = :code')
            ->setParameter('code', $code);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Discipline partagent le même code [".$code."]");
        }
        return $result;
    }

    /**
     * @param string $champ
     * @param string $ordre
     * @return Discipline[]
     */
    public function getDisciplines(string $champ = 'code', string $ordre = 'ASC') : array
    {
        $qb = $this->createQueryBuilder()
            ->orderBy('discipline.'. $champ, $ordre);
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param string $champ
     * @param string $ordre
     * @return array
     */
    public function getDisciplinesAsOptions(string $champ = 'code', string $ordre = 'ASC') : array
    {
        $result = $this->getDisciplines($champ, $ordre);
        $options = [];
        foreach ($result as $item) {
            //$options[$item->getCode()] = $item->getLibelle();
            $options[$item->getLibelle()] = $item->getLibelle();
        }
        return $options;
    }

}