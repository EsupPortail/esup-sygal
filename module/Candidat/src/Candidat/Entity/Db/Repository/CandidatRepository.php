<?php

namespace Candidat\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Candidat\Entity\Db\Candidat;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class CandidatRepository extends DefaultEntityRepository
{
    /**
     * @param string $sourceCode
     * @return Candidat|null
     */
    public function findOneBySourceCode(string $sourceCode): ?Candidat
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->addSelect('i')
            ->join('c.individu', 'i')
            ->where('c.sourceCode = :sourceCode')
            ->andWhere('c.histoDestruction is null')
            ->setParameter('sourceCode', $sourceCode);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs candidats ont été trouvés avec le même source code: " . $sourceCode);
        }
    }

    /**
     * Recherche d'un candidat par son individu lié.
     */
    public function findOneByIndividu(Individu $individu): ?Candidat
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->addSelect('i')
            ->join('c.individu', 'i', Join::WITH, 'i = :individu')->setParameter('individu', $individu)
            ->join('c.source', 's')
            ->andWhereNotHistorise()
            ->addOrderBy('s.id', 'asc') // source SyGAL privilégiée
            ->addOrderBy('c.histoCreation', 'desc')      // créé ou
            ->addOrderBy('c.histoModification', 'desc'); // modifié le plus récemment

        return $qb->getQuery()->setMaxResults(1)->getResult()[0] ?? null;
    }

    /**
     * Recherche textuelle de candidats à l'aide de la table INDIVIDU_RECH, en SQL pur.
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
            "SELECT i.*, c.*/*indispensable*/, src.libelle as source_libelle, src.importable as source_importable " .
            "FROM INDIVIDU i " .
            "JOIN INDIVIDU_RECH ir on ir.id = i.id " .
            "JOIN CANDIDAT_HDR c on c.individu_id = c.id " .
            "JOIN SOURCE src on src.id = c.source_id " .
            "WHERE c.HISTO_DESTRUCTION IS NULL";

        $sqlCri = [];
        foreach ($criteres as $c) {
            $sqlCri[] = "(concat(ir.haystack,' ',c.ine)) LIKE str_reduce($$%" . $c . "%$$)";
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
                "Erreur rencontrée lors de l'exécution de la requête de recherche de candidat", null, $e);
        }

        try {
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new RuntimeException(
                "Impossible d'obtenir les résultats de la requête de recherche de candidat", null, $e);
        }
    }

    /**
     * Recherche des candidats par ED et Etablissement.
     *
     * @param EcoleDoctorale|string|null $ecoleDoctorale ED, code structure ou critères de recherche de l'ED
     * @param Etablissement|null $etablissement Etablissement éventuel
     * @return Candidat[]
     */
    public function findByEcoleDoctAndEtab($ecoleDoctorale = null, Etablissement $etablissement = null): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->addSelect('i, h, ed, s')
            ->join('c.individu', 'i')
            ->join('c.hdrs', 't', Join::WITH, 'h.etatHDR = :etat')->setParameter('etat', HDR::ETAT_EN_COURS)
            ->join('h.ecoleDoctorale', 'ed')
            ->join('ed.structure', 's')
            ->andWhere('c.histoDestruction is null')
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