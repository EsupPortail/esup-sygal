<?php

namespace Doctorant\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class DoctorantRepository extends DefaultEntityRepository
{
    /**
     * @param string $sourceCode
     * @return \Doctorant\Entity\Db\Doctorant|null
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
     * Recherche d'un doctorant par son individu lié.
     *
     * *Attention : il peut arriver que 2 doctorants soient liés au même individu (D1->I3 et D2->I3) ! Oui, cela arrive
     * lorsque les 2 individus auxquels était lié chacun des 2 doctorants respectivement (I1 et I2) ont été détectés
     * comme doublon et substitués par I3 par le moteur de substitutions (dédoublonnage).
     * On pourrait s'attendre à ce que les 2 doctorants D1 et D2 aient également été substitués puisqu'ils sont
     * liés au même individu, mais ce n'est pas le cas s'ils n'ont pas le même INE (l'INE est l'attribut discriminant les
     * doctorants pour le moteur de substitutions)... et cela arrive ! On privilégie systématiquement le Doctorant
     * créé/modifié le plus récemment.*
     */
    public function findOneByIndividu(Individu $individu): ?Doctorant
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->addSelect('i')
            ->join('d.individu', 'i', Join::WITH, 'i = :individu')->setParameter('individu', $individu)
            ->join('d.source', 's')
            ->andWhereNotHistorise()
            ->addOrderBy('s.id', 'asc') // source SyGAL privilégiée
            ->addOrderBy('d.histoCreation', 'desc')      // créé ou
            ->addOrderBy('d.histoModification', 'desc'); // modifié le plus récemment

        return $qb->getQuery()->setMaxResults(1)->getResult()[0] ?? null;
    }

    /**
     * Recherche textuelle de doctorants à l'aide de la table INDIVIDU_RECH, en SQL pur.
     *
     * @param string $text
     * @param integer $limit
     *
     * @return array[]
     */
    public function findByText(string $text, int $limit = 0): array
    {
        if (strlen($text) < 2) return [];

        $text = Util::reduce($text);
        $criteres = explode(' ', $text);

        $sql =
            "SELECT i.*, d.*/*indispensable*/, src.libelle as source_libelle, src.importable as source_importable " .
            "FROM INDIVIDU i " .
            "JOIN INDIVIDU_RECH ir on ir.id = i.id " .
            "JOIN DOCTORANT d on d.individu_id = i.id " .
            "JOIN SOURCE src on src.id = d.source_id " .
            "WHERE d.HISTO_DESTRUCTION IS NULL";

        $sqlCri = [];
        foreach ($criteres as $c) {
            $sqlCri[] = "(concat(ir.haystack,' ',d.ine)) LIKE str_reduce($$%" . $c . "%$$)";
        }
        $sqlCri = implode(' AND ', $sqlCri);

        $sql .= ' AND (' . $sqlCri . ') ';

        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }

        try {
            $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (Exception $e) {
            throw new RuntimeException(
                "Erreur rencontrée lors de l'exécution de la requête de recherche de doctorant", null, $e);
        }

        try {
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new RuntimeException(
                "Impossible d'obtenir les résultats de la requête de recherche de doctorant", null, $e);
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
                    ->andWhere('s = :structure')
                    ->setParameter('structure', $ecoleDoctorale->getStructure(/*false*/));
            } elseif (is_array($ecoleDoctorale)) {
                $leftPart = key($ecoleDoctorale);
                $rightPart = current($ecoleDoctorale);
                $qb
                    ->andWhere(sprintf($leftPart, 's') . ' = :value')
                    ->setParameter('value', $rightPart);
            } else {
                $qb
                    ->andWhere('s.code = :code')
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