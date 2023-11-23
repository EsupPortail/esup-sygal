<?php

namespace Doctorant\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class DoctorantRepository extends DefaultEntityRepository
{
    /**
     * @param string $sourceCode
     * @return Doctorant
     */
    public function findOneBySourceCode(string $sourceCode): ?Doctorant
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->addSelect('i')
            ->join('t.individu', 'i')
            ->where('t.sourceCode = :sourceCode')
            ->andWhere('t.histoDestruction is null')
            ->setParameter('sourceCode', $sourceCode);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs doctorants ont été trouvés avec le même source code: " . $sourceCode);
        }
    }

    /**
     * @param Individu $individu
     * @return Doctorant|null
     */
    public function findOneByIndividu(Individu $individu): ?Doctorant
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->addSelect('i')
            ->join('d.individu', 'i', Join::WITH, 'i = :individu')
            ->andWhereNotHistorise()
            ->setParameter('individu', $individu);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie rencontrée : 2 doctorants liés au même individu !");
        }
    }

    /**
     * Recherche des doctorants par ED et Etablissement.
     *
     * @param EcoleDoctorale|string|null $ecoleDoctorale ED, code structure ou critères de recherche de l'ED
     * @param Etablissement|null $etablissement Etablissement éventuel
     * @return Doctorant[]
     */
    public function findByEcoleDoctAndEtab($ecoleDoctorale = null, Etablissement $etablissement = null): array
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->addSelect('i, t, ed, s')
            ->join('d.individu', 'i')
            ->join('d.theses', 't', Join::WITH, 't.etatThese = :etat')->setParameter('etat', These::ETAT_EN_COURS)
            ->join('t.ecoleDoctorale', 'ed')
            ->join('ed.structure', 's')
            ->andWhere('d.histoDestruction is null')
            ->addOrderBy('i.nomUsuel, i.prenom1');

        if ($ecoleDoctorale !== null) {
            if ($ecoleDoctorale instanceof EcoleDoctorale) {
                $qb
                    ->andWhere('s = :structure OR structureSubstituante = :structure')
                    ->setParameter('structure', $ecoleDoctorale->getStructure(/*false*/));
            } elseif (is_array($ecoleDoctorale)) {
                $leftPart = key($ecoleDoctorale);
                $rightPart = current($ecoleDoctorale);
                $qb
                    ->andWhere(sprintf($leftPart, 's') . ' = :value OR ' . sprintf($leftPart, 'structureSubstituante'). ' = :value')
                    ->setParameter('value', $rightPart);
            } else {
                $qb
                    ->andWhere('s.code = :code OR structureSubstituante.code = :code')
                    ->setParameter('code', $ecoleDoctorale);
            }
        }

        if ($etablissement !== null) {
            $qb
                ->join('t.etablissement', 'e')->addSelect('e')
                ->join('e.structure', 'etab_structure')->addSelect('etab_structure')
                ->andWhereStructureIs($etablissement->getStructure(), 'etab_structure');
        }

        return $qb->getQuery()->getResult();
    }
}