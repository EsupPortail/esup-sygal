<?php

namespace Import\Model\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;
use Application\Entity\Db\These;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;

class ImportObservResultRepository extends DefaultEntityRepository
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * Recherche des résultats d'observation d'import des thèses.
     *
     * @param \Import\Model\ImportObserv $importObserv
     * @param These|null $these
     * @return \Import\Model\ImportObservResult[]
     */
    public function fetchImportObservResults(ImportObserv $importObserv, These $these = null)
    {
        switch ($importObserv->getCode()) {
            case ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS:
            case ImportObserv::CODE_CORRECTION_PASSE_A_FACULTATIVE:
                $qb = $this->createImportObservResultsQueryBuilder($importObserv, $these);
                $qb->andWhere('ior.dateNotif is null'); // aucune notification ne doit avoir été faite
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_OBLIGATOIRE:
                $qb = $this->createImportObservResultsQueryBuilder($importObserv, $these);
                $qb->andWhere('ior.dateNotif is null OR ior.dateNotif is not null'); // une notif peut avoir été faite ou non
                break;
            default:
                throw new RuntimeException("Cas non prévu!");
                break;
        }

        /** @var ImportObservResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * @param ImportObserv $importObserv
     * @param These|null $these
     * @return QueryBuilder
     */
    private function createImportObservResultsQueryBuilder(ImportObserv $importObserv, These $these = null)
    {
        $qb = $this->createQueryBuilder('ior')
            ->addSelect('io')
            ->join('ior.importObserv', 'io', Join::WITH, 'io = :io')
            ->andWhere('io.enabled = true')
            ->setParameter('io', $importObserv);

        if ($these !== null) {
            $qb
                ->andWhere('ior.sourceCode = :sourceCode')
                ->setParameter('sourceCode', $these->getSourceCode());
        }

        return $qb;
    }
}