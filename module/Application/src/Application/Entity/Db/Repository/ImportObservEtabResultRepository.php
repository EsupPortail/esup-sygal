<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\ImportObserv;
use Application\Entity\Db\ImportObservEtabResult;
use Application\Entity\Db\These;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;

class ImportObservEtabResultRepository extends DefaultEntityRepository
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * Recherche des résultats d'observation d'import des thèses.
     *
     * @param ImportObserv         $importObserv
     * @param Etablissement|string $etablissement Etablissement concerné
     * @param These|null           $these
     * @return ImportObservEtabResult[]
     */
    public function fetchImportObservEtabResults(ImportObserv $importObserv, $etablissement, These $these = null)
    {
        switch ($importObserv->getCode()) {
            case ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS:
                return $this->fetchImportObservEtabResultsForResultatAdmis($etablissement, $these);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_FACULTATIVE:
                return $this->fetchImportObservEtabResultsForCorrectionFacultative($etablissement, $these);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_OBLIGATOIRE:
                return $this->fetchImportObservEtabResultsForCorrectionObligatoire($etablissement, $these);
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
     * @return ImportObservEtabResult[]
     */
    private function fetchImportObservEtabResultsForResultatAdmis($etablissement, These $these = null)
    {
        $qb = $this->createImportObservEtabResultsQueryBuilder(ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS, $etablissement, $these);
        $qb->andWhere('ioer.dateNotif is null'); // aucune notification ne doit avoir été faite

        /** @var ImportObservEtabResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * Recherche des résultats d'observation des thèses dont le flag "correction_autorisee" est passé à "facultative".
     *
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     * @return ImportObservEtabResult[]
     */
    private function fetchImportObservEtabResultsForCorrectionFacultative($etablissement, These $these = null)
    {
        $qb = $this->createImportObservEtabResultsQueryBuilder(ImportObserv::CODE_CORRECTION_PASSE_A_FACULTATIVE, $etablissement, $these);
        $qb->andWhere('ioer.dateNotif is null'); // aucune notification ne doit avoir été faite

        /** @var ImportObservEtabResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * Recherche des résultats d'observation des thèses dont le flag "correction_autorisee" est passé à "obligatoire".
     *
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     * @return ImportObservEtabResult[]
     */
    private function fetchImportObservEtabResultsForCorrectionObligatoire($etablissement, These $these = null)
    {
        $qb = $this->createImportObservEtabResultsQueryBuilder(ImportObserv::CODE_CORRECTION_PASSE_A_OBLIGATOIRE, $etablissement, $these);
        $qb->andWhere('ioer.dateNotif is null OR ioer.dateNotif is not null'); // une notif peut avoir été faite ou non

        /** @var ImportObservEtabResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * @param string               $importObservCode ex: ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     * @return QueryBuilder
     */
    private function createImportObservEtabResultsQueryBuilder($importObservCode, $etablissement, These $these = null)
    {
        $etablissement = $etablissement instanceof Etablissement ?
            $etablissement->getStructure()->getCode() :
            $etablissement;

        $qb = $this->createQueryBuilder('ioer')
            ->join('ioer.importObservEtab', 'ioe')
            ->join('ioe.importObserv', 'io', Join::WITH, 'io.code = :code')
            ->join('ioe.etablissement', 'e', Join::WITH, 'e.sourceCode = :etab')
            ->andWhere('ioe.enabled = 1')
            ->setParameter('code', $importObservCode)
            ->setParameter('etab', $etablissement);

        if ($these !== null) {
            $qb
                ->andWhere('ioer.sourceCode = :sourceCode')
                ->setParameter('sourceCode', $these->getSourceCode());
        }

        return $qb;
    }
}