<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Doctrine\ORM\NonUniqueResultException;

class DoctorantRepository extends DefaultEntityRepository
{
    /**
     * @param $username
     * @param string $etablissement
     * @return Doctorant
     * @throws NonUniqueResultException
     */
    public function findOneByUsernameAndEtab($username, $etablissement)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->leftJoin('t.complements', 'c')
            ->andWhere('1 = pasHistorise(t)')
            // todo: ajouter le code étab au persopass enregistré dans la table DOCTORANT_COMPL
            ->andWhere('t.sourceCode = :sourceCode OR c.persopass = :persopass')
            ->setParameter('sourceCode', $etablissement . '::' . $username)
            ->setParameter('persopass', $username);

        return $qb->getQuery()->getOneOrNullResult();
    }
}