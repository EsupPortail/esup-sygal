<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\ImportObserv;
use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\These;
use Application\Filter\EtablissementPrefixFilter;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;

class ImportObservResultRepository extends DefaultEntityRepository
{
    /**
     * Recherche des résultats d'observation d'import des thèses.
     *
     * @param ImportObserv         $importObserv
     * @param Etablissement|string $etablissement Etablissement concerné
     * @param These|null           $these
     * @return ImportObservResult[]
     */
    public function fetchImportObservResults(ImportObserv $importObserv, $etablissement, These $these = null)
    {
        switch ($importObserv->getCode()) {
            case ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS:
                return $this->fetchImportObservResultsForResultatAdmis($etablissement, $these);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_MINEURE:
                return $this->fetchImportObservResultsForCorrectionMineure($etablissement, $these);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_MAJEURE:
                return $this->fetchImportObservResultsForCorrectionMajeure($etablissement, $these);
                break;
            default:
                throw new RuntimeException("Cas non prévu!");
                break;
        }
    }

    /**
     * Recherche des résultats d'observation des thèses dont le résultat est passé à "admis".
     *
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     * @return ImportObservResult[]
     */
    private function fetchImportObservResultsForResultatAdmis($etablissement, These $these = null)
    {
        $qb = $this->createImportObservResultsQueryBuilder(ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS, $etablissement, $these);
        $qb->andWhere('ior.dateNotif is null'); // aucune notification ne doit avoir été faite

        /** @var ImportObservResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * Recherche des résultats d'observation des thèses dont le flag "correction_autorisee" est passé à "mineure".
     *
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     * @return ImportObservResult[]
     */
    private function fetchImportObservResultsForCorrectionMineure($etablissement, These $these = null)
    {
        $qb = $this->createImportObservResultsQueryBuilder(ImportObserv::CODE_CORRECTION_PASSE_A_MINEURE, $etablissement, $these);
        $qb->andWhere('ior.dateNotif is null'); // aucune notification ne doit avoir été faite

        /** @var ImportObservResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * Recherche des résultats d'observation des thèses dont le flag "correction_autorisee" est passé à "majeure".
     *
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     * @return ImportObservResult[]
     */
    private function fetchImportObservResultsForCorrectionMajeure($etablissement, These $these = null)
    {
        $qb = $this->createImportObservResultsQueryBuilder(ImportObserv::CODE_CORRECTION_PASSE_A_MAJEURE, $etablissement, $these);
        $qb->andWhere('ior.dateNotif is null OR ior.dateNotif is not null'); // une notif peut avoir été faite ou non

        /** @var ImportObservResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * @param string               $importObservCode ex: ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     * @return QueryBuilder
     */
    private function createImportObservResultsQueryBuilder($importObservCode, $etablissement, These $these = null)
    {
        $qb = $this->createQueryBuilder('ior')
            ->join('ior.importObserv', 'io', Join::WITH, 'io.code = :code')
            ->andWhere('io.enabled = 1')
            ->setParameter('code', $importObservCode);

        if ($etablissement instanceof Etablissement) {
            $etablissement = $etablissement->getStructure()->getCode();
        }

        $f = new EtablissementPrefixFilter();
        $sourceCodePattern = $f->generateSourceCodeSearchPatternForThisEtablissement($etablissement);

        $qb
            ->andWhere('ior.sourceCode like :pattern')
            ->setParameter('pattern', $sourceCodePattern);

        if ($these !== null) {
            $qb
                ->andWhere('ior.sourceCode = :sourceCode')
                ->setParameter('sourceCode', $these->getSourceCode());
        }

        return $qb;
    }
}