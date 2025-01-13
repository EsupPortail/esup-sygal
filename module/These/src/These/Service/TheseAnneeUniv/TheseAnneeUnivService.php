<?php

namespace These\Service\TheseAnneeUniv;

use Application\Entity\Db\AutorisationInscription;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr\Join;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\TheseAnneeUniv;
use UnicaenApp\Exception\RuntimeException;

class TheseAnneeUnivService extends BaseService
{
    use SourceServiceAwareTrait;

    /**
     * @return \These\Entity\Db\Repository\TheseAnneeUnivRepository
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

    public function newTheseAnneeUniv(int $premiereAnnee): TheseAnneeUniv
    {
        $theseAnneeUniv = new TheseAnneeUniv();
        $theseAnneeUniv->setAnneeUniv($premiereAnnee);
//        $theseAnneeUniv->setThese();
        $theseAnneeUniv->setSource($this->sourceService->fetchApplicationSource());
        $theseAnneeUniv->setSourceCode($this->sourceService->genereateSourceCode());

        return $theseAnneeUniv;
    }

    public function save(TheseAnneeUniv $entity): void
    {
        try {
            if ($entity->getId() === null) {
                $this->entityManager->persist($entity);
            }
            $this->entityManager->flush($entity);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BDD !", null, $e);
        }
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