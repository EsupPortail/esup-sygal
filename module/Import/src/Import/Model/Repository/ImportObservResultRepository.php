<?php

namespace Import\Model\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;
use UnicaenApp\Exception\RuntimeException;

class ImportObservResultRepository extends DefaultEntityRepository
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * Recherche des résultats d'observation d'import des thèses.
     *
     * @return \Import\Model\ImportObservResult[]
     */
    public function fetchImportObservResults(ImportObserv $importObserv, array $criteria = []): array
    {
        switch ($importObserv->getCode()) {
            case ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS:
            case ImportObserv::CODE_CORRECTION_PASSE_A_FACULTATIVE:
                $qb = $this->createImportObservResultsQueryBuilder($importObserv, $criteria);
                $qb->andWhere('ior.dateNotif is null'); // aucune notification ne doit avoir été faite
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_OBLIGATOIRE:
                $qb = $this->createImportObservResultsQueryBuilder($importObserv, $criteria);
                $qb->andWhere('ior.dateNotif is null OR ior.dateNotif is not null'); // une notif peut avoir été faite ou non
                break;
            default:
                throw new RuntimeException("Cas non prévu!");
        }

        /** @var ImportObservResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    private function createImportObservResultsQueryBuilder(ImportObserv $importObserv, array $criteria = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ior')
            ->addSelect('io')
            ->join('ior.importObserv', 'io', Join::WITH, 'io = :io')
            ->andWhere('io.enabled = true')
            ->setParameter('io', $importObserv);

        /** @var \Application\Entity\Db\Source|null $source */
        $source = $criteria['source'] ?? null;
        if ($source !== null) {
            $qb
                ->andWhere('ior.source = :source')
                ->setParameter('source', $source->getId());
        }

        /** @var \These\Entity\Db\These|null $these */
        $these = $criteria['these'] ?? null;
        if ($these !== null) {
            $qb
                ->andWhere('ior.sourceCode = :sourceCode')
                ->setParameter('sourceCode', $these->getSourceCode());
        }

        return $qb;
    }
}