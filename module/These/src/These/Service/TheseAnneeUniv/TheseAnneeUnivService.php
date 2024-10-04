<?php

namespace These\Service\TheseAnneeUniv;

use Application\Entity\Db\AutorisationInscription;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\TheseAnneeUniv;

class TheseAnneeUnivService extends BaseService
{
    use SourceServiceAwareTrait;
    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(TheseAnneeUniv::class);
    }

    /**
     * @param Etablissement|null $etablissement
     * @param bool $cacheable
     * @return int[]
     */
    public function fetchDistinctAnneesUniv1ereInscription(Etablissement $etablissement = null, $cacheable = false): array
    {
        $qb = $this->getRepository()->createQueryBuilder('t');
        $qb
            ->distinct()
            ->select("t.anneeUniv")
            ->orderBy("t.anneeUniv");

        if ($etablissement !== null) {
            $qb
                ->join('t.these', 'th')
                ->join('th.etablissement', 'etab', Join::WITH, 'etab = :etablissement')
                ->setParameter('etablissement', $etablissement);
        }

        $qb->setCacheable($cacheable);

        return array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());
    }

    public function initFromAutorisationInscription(AutorisationInscription $autorisationInscription): TheseAnneeUniv
    {
        $theseAnneeUnivPremiereInscription = new TheseAnneeUniv();
        $theseAnneeUnivPremiereInscription->setAnneeUniv($autorisationInscription->getAnneeUniv()->getPremiereAnnee());
        $theseAnneeUnivPremiereInscription->setThese($autorisationInscription->getThese());
        $theseAnneeUnivPremiereInscription->setSource($this->sourceService->fetchApplicationSource());
        $theseAnneeUnivPremiereInscription->setSourceCode($this->sourceService->genereateSourceCode());

        return $theseAnneeUnivPremiereInscription;
    }
}