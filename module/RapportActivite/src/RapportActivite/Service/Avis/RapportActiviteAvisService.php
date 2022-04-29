<?php

namespace RapportActivite\Service\Avis;

use Application\Service\BaseService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Notification\RapportActiviteAvisNotification;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class RapportActiviteAvisService extends BaseService
{
    use EtablissementServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use AvisServiceAwareTrait;

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(RapportActiviteAvis::class);
    }

    /**
     * @return AvisType[]
     */
    public function findAllAvisTypes(): array
    {
        $avisTypes = [];
        foreach (RapportActiviteAvis::AVIS_TYPE__CODES_ORDERED as $code) {
            $avisTypes[$code] = $this->avisService->findOneAvisTypeByCode($code);
        }

        return $avisTypes;
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     */
    public function findRapportAvisById($id): RapportActiviteAvis
    {
        $qb = $this->getRepository()->createQueryBuilder('ra')
            ->addSelect('r')
            ->join('ra.rapport', 'r')
            ->where('ra = :id')->setParameter('id', $id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Impossible mais vrai !");
        }
    }

    /**
     * Retourne le type de l'avis précédant le dernier avis apporté sur le rapport spécifié,
     * ou `null` si un seul ou aucun avis n'a été apporté.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function findPreviousAvisTypeForRapport(RapportActivite $rapport): ?AvisType
    {
        $last = $this->findMostRecentAvisTypeForRapport($rapport);
        if ($last === null) {
            return null;
        }

        $prev = null;
        foreach (RapportActiviteAvis::AVIS_TYPE__CODES_ORDERED as $code) {
            $avisType = $this->avisService->findOneAvisTypeByCode($code);
            $rapportActiviteAvis = $this->findRapportAvisByRapportAndAvisType($rapport, $avisType);
            if ($rapportActiviteAvis->getAvis()->getAvisType() === $last) {
                break;
            }
            $prev = $avisType;
        }

        return $prev;
    }

    /**
     * Retourne l'avis le plus récent apporté sur le rapport spécifié,
     * ou `null` si aucun avis n'a été apporté.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function findMostRecentRapportAvisForRapport(RapportActivite $rapport): ?RapportActiviteAvis
    {
        $prevRapportActiviteAvis = null;
        foreach (RapportActiviteAvis::AVIS_TYPE__CODES_ORDERED as $code) {
            $avisType = $this->avisService->findOneAvisTypeByCode($code);
            $rapportActiviteAvis = $this->findRapportAvisByRapportAndAvisType($rapport, $avisType);
            if ($rapportActiviteAvis === null) {
                break;
            }
            $prevRapportActiviteAvis = $rapportActiviteAvis;
        }

        return $prevRapportActiviteAvis;
    }

    /**
     * Retourne le type du dernier avis apporté sur le rapport spécifié,
     * ou `null` si aucun avis n'a été apporté.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function findMostRecentAvisTypeForRapport(RapportActivite $rapport): ?AvisType
    {
        $last = null;
        foreach (RapportActiviteAvis::AVIS_TYPE__CODES_ORDERED as $code) {
            $avisType = $this->avisService->findOneAvisTypeByCode($code);
            $rapportActiviteAvis = $this->findRapportAvisByRapportAndAvisType($rapport, $avisType);
            if ($rapportActiviteAvis === null) {
                break;
            }
            $last = $avisType;
        }

        return $last;
    }

    /**
     * Recherche le prochain type d'avis disponible/possible pour le rapport spécifié.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \UnicaenAvis\Entity\Db\AvisType|null
     */
    public function findNextExpectedAvisTypeForRapport(RapportActivite $rapport): ?AvisType
    {
        foreach (RapportActiviteAvis::AVIS_TYPE__CODES_ORDERED as $code) {
            $avisType = $this->avisService->findOneAvisTypeByCode($code);
            $rapportActiviteAvis = $this->findRapportAvisByRapportAndAvisType($rapport, $avisType);
            if ($rapportActiviteAvis === null) {
                return $avisType;
            }
        }

        return null;
    }

    public function findRapportAvisByRapportAndAvisType(RapportActivite $rapportActivite, AvisType $avisType): ?RapportActiviteAvis
    {
        $qb = $this->getRepository()->createQueryBuilder('ra')
            ->addSelect('r, a, at')
            ->join('ra.rapport', 'r', Join::WITH, 'r = :rapport')->setParameter('rapport', $rapportActivite)
            ->join('ra.avis', 'a')
            ->join('a.avisType', 'at', Join::WITH, 'at = :avisType')->setParameter('avisType', $avisType)
            ->andWhereNotHistorise();

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Impossible mais vrai !");
        }
    }

    /**
     * @param RapportActiviteAvis $rapportAvis
     */
    public function deleteRapportAvis(RapportActiviteAvis $rapportAvis)
    {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->remove($rapportAvis);
            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();
            $this->avisService->deleteAvis($rapportAvis->getAvis());
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression de l'avis, rollback!", 0, $e);
        }
    }

    public function saveRapportAvis(RapportActiviteAvis $rapportAvis)
    {
        $this->entityManager->beginTransaction();

        $rapport = $rapportAvis->getRapportActivite();
        $rapport->addRapportAvis($rapportAvis);

        try {
            $this->avisService->saveAvis($rapportAvis->getAvis());

            $this->entityManager->persist($rapportAvis);
            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", 0, $e);
        }
    }

    public function deleteAllAvisForRapportActivite(RapportActivite $rapportActivite)
    {
        try {
            foreach ($rapportActivite->getRapportAvis() as $rapportAvis) {
                $rapportActivite->removeRapportAvis($rapportAvis);
                $this->getEntityManager()->remove($rapportAvis);
                $this->getEntityManager()->flush($rapportAvis);
            }
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
        }
    }

    public function createRapportActiviteAvisNotification(RapportActiviteAvis $rapportActiviteAvis): RapportActiviteAvisNotification
    {
        $notif = new RapportActiviteAvisNotification();
        $notif->setRapportActiviteAvis($rapportActiviteAvis);

        return $notif;
    }
}