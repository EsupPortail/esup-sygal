<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\These;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;

class DoctorantRepository extends DefaultEntityRepository
{
    /**
     * @param string        $sourceCode
     * @return Doctorant
     * @throws NonUniqueResultException
     */
    public function findOneBySourceCode($sourceCode)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->addSelect('i')
            ->join('t.individu', 'i')
            ->where('t.sourceCode = :sourceCode')
            ->andWhere('1 = pasHistorise(t)')
            ->setParameter('sourceCode', $sourceCode);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Individu $individu
     * @return Doctorant|null
     * @throws NonUniqueResultException
     */
    public function findOneByIndividu(Individu $individu)
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->addSelect('i')
            ->join('d.individu', 'i', Join::WITH, 'i = :individu')
            ->setParameter('individu', $individu);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param EcoleDoctorale|string|null $ecoleDoctorale ED ou code structure de l'ED
     * @param Etablissement|null $etablissement Etablissement éventuel
     * @param string $etatThese Par défaut {@see These::ETAT_EN_COURS]
     * @return Doctorant[]
     */
    public function findByEcoleDoctAndEtab($ecoleDoctorale = null, Etablissement $etablissement = null, $etatThese = These::ETAT_EN_COURS)
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->addSelect('i')
            ->join('d.individu', 'i')
            ->join('d.theses', 't', Join::WITH, 't.etatThese = :etat')->setParameter('etat', $etatThese)
            ->join('t.ecoleDoctorale', 'ed')
            ->join('ed.structure', 's')
            ->andWhere('1 = pasHistorise(d)')
            ->addOrderBy('i.nomUsuel')
            ->addOrderBy('i.prenom1')
        ;

        if ($ecoleDoctorale !== null) {
            if ($ecoleDoctorale instanceof EcoleDoctorale) {
                $qb->andWhere('ed = :ed');
            } elseif (is_array($ecoleDoctorale)) {
                $qb->andWhere(key($ecoleDoctorale) . ' = :ed');
                $ecoleDoctorale = current($ecoleDoctorale);
            } else {
                $qb->andWhere('s.code = :ed');
            }
            $qb->setParameter('ed', $ecoleDoctorale);
        }

        if ($etablissement !== null) {
            $qb->join('t.etablissement', 'e', Join::WITH, 'e = :etab')->setParameter('etab', $etablissement);
        }

        return $qb->getQuery()->getResult();
    }
}