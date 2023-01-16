<?php

namespace RapportActivite\Service\Avis;

use Application\Service\BaseService;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use Laminas\EventManager\EventManagerAwareTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Event\Avis\RapportActiviteAvisEvent;
use RapportActivite\Notification\RapportActiviteAvisNotification;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class RapportActiviteAvisService extends BaseService
{
    use EtablissementServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use AvisServiceAwareTrait;
    use EventManagerAwareTrait;

    public const RAPPORT_ACTIVITE__AVIS_AJOUTE__EVENT = 'RAPPORT_ACTIVITE__AVIS_AJOUTE__EVENT';
    public const RAPPORT_ACTIVITE__AVIS_MODIFIE__EVENT = 'RAPPORT_ACTIVITE__AVIS_MODIFIE__EVENT';
    public const RAPPORT_ACTIVITE__AVIS_SUPPRIME__EVENT = 'RAPPORT_ACTIVITE__AVIS_SUPPRIME__EVENT';

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
    public function findAllSortedAvisTypes(): array
    {
        return $this->avisService->findAvisTypesByCodes(RapportActiviteAvis::AVIS_TYPE__CODES);
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

        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes(RapportActiviteAvis::AVIS_TYPE__CODES);

        $prev = null;
        foreach ($allSortedAvisTypes as $avisType) {
            $rapportActiviteAvis = $this->findRapportAvisByRapportAndAvisType($rapport, $avisType);
            if ($rapportActiviteAvis->getAvis()->getAvisType() === $last) {
                break;
            }
            $prev = $avisType;
        }

        return $prev;
    }

    /**
     * Recherche l'éventuel avis fourni avant celui spécifié.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function findRapportAvisBefore(RapportActiviteAvis $rapportActiviteAvis): ?RapportActiviteAvis
    {
        $rapport = $rapportActiviteAvis->getRapportActivite();

        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes(RapportActiviteAvis::AVIS_TYPE__CODES);
        $previousAvisType = null;
        foreach ($allSortedAvisTypes as $avisType) {
            if ($rapportActiviteAvis->getAvis()->getAvisType() === $avisType) {
                break;
            }
            $previousAvisType = $avisType;
        }

        if ($previousAvisType !== null) {
            return $this->findRapportAvisByRapportAndAvisType($rapport, $previousAvisType);
        }

        return null;
    }

    /**
     * Recherche l'éventuel avis fourni après celui spécifié.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function findRapportAvisAfter(RapportActiviteAvis $rapportActiviteAvis): ?RapportActiviteAvis
    {
        $rapport = $rapportActiviteAvis->getRapportActivite();

        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes(RapportActiviteAvis::AVIS_TYPE__CODES);
        $nextAvisType = null;
        $found = false;
        foreach ($allSortedAvisTypes as $avisType) {
            if ($found) {
                $nextAvisType = $avisType;
                break;
            }
            if ($rapportActiviteAvis->getAvis()->getAvisType() === $avisType) {
                $found = true;
            }
        }

        if ($nextAvisType !== null) {
            return $this->findRapportAvisByRapportAndAvisType($rapport, $nextAvisType);
        }

        return null;
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
        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes(RapportActiviteAvis::AVIS_TYPE__CODES);

        $prevRapportActiviteAvis = null;
        foreach ($allSortedAvisTypes as $avisType) {
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
        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes(RapportActiviteAvis::AVIS_TYPE__CODES);

        $last = null;
        foreach ($allSortedAvisTypes as $avisType) {
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
     * ATTENTION ! Lors du fetch des rapports :
     * - Les relations suivantes doivent avoir été sélectionnées : 'rapportAvis->avis->avisType' ;
     * - L'orderBy 'avisType.ordre' doit avoir été utilisé.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \UnicaenAvis\Entity\Db\AvisType|null
     */
    public function findExpectedAvisTypeForRapport(RapportActivite $rapport): ?AvisType
    {
        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes(RapportActiviteAvis::AVIS_TYPE__CODES);

        /** @var AvisType[] $avisTypesCodesApportes */
        $avisTypesCodesApportes = array_map(
            fn(RapportActiviteAvis $ra) => $ra->getAvis()->getAvisType()->getCode(),
            $rapport->getRapportAvis()->toArray()
        );

        foreach ($allSortedAvisTypes as $avisType) {
            if (!in_array($avisType->getCode(), $avisTypesCodesApportes)) {
                return $avisType;
            }
        }

        return null;
    }

    public function findRapportAvisByRapportAndAvisType(RapportActivite $rapportActivite, AvisType $avisType): ?RapportActiviteAvis
    {
        $qb = $this->getRepository()->createQueryBuilder('ra')
            ->addSelect('r, a')
            ->join('ra.rapport', 'r', Join::WITH, 'r = :rapport')->setParameter('rapport', $rapportActivite)
            ->join('ra.avis', 'a')
            ->andWhere('a.avisType = :avisType')->setParameter('avisType', $avisType)
            ->andWhereNotHistorise();

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Impossible mais vrai !");
        }
    }

    /**
     * Supprime en bdd un avis sur un rapport d'activité, AVEC déclenchement d'événement.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportAvis
     * @return \RapportActivite\Event\Avis\RapportActiviteAvisEvent
     */
    public function deleteRapportAvis(RapportActiviteAvis $rapportAvis): RapportActiviteAvisEvent
    {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->remove($rapportAvis);
            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();
            $this->avisService->deleteAvis($rapportAvis->getAvis());

            // déclenchement d'un événement
            $event = $this->triggerEvent(
                self::RAPPORT_ACTIVITE__AVIS_SUPPRIME__EVENT,
                $rapportAvis,
                []
            );
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression de l'avis, rollback!", 0, $e);
        }

        return $event;
    }

    public function newRapportAvis(RapportActivite $rapportActivite): RapportActiviteAvis
    {
        $rapportActiviteAvis = new RapportActiviteAvis();
        $rapportActiviteAvis
            ->setRapportActivite($rapportActivite);

        return $rapportActiviteAvis;

    }

    /**
     * Enregistre en bdd un nouvel avis sur un rapport d'activité, AVEC déclenchement d'événement.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportAvis
     * @return \RapportActivite\Event\Avis\RapportActiviteAvisEvent
     */
    public function saveNewRapportAvis(RapportActiviteAvis $rapportAvis): RapportActiviteAvisEvent
    {
        $this->entityManager->beginTransaction();

        $rapport = $rapportAvis->getRapportActivite();
        $rapport->addRapportAvis($rapportAvis);

        try {
            $this->avisService->saveAvis($rapportAvis->getAvis());

            $this->entityManager->persist($rapportAvis);
            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();

            // déclenchement d'un événement
            $event = $this->triggerEvent(
                self::RAPPORT_ACTIVITE__AVIS_AJOUTE__EVENT,
                $rapportAvis,
                []
            );
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", 0, $e);
        }

        return $event;
    }

    /**
     * Met à jour en bdd un avis sur un rapport d'activité, AVEC déclenchement d'événement.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportAvis
     * @return \RapportActivite\Event\Avis\RapportActiviteAvisEvent
     */
    public function updateRapportAvis(RapportActiviteAvis $rapportAvis): RapportActiviteAvisEvent
    {
        $this->entityManager->beginTransaction();

        try {
            $this->avisService->saveAvis($rapportAvis->getAvis());

            $rapportAvis->setHistoModification(new DateTime());
            $rapportAvis->setHistoModificateur();

            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();

            // déclenchement d'un événement
            $event = $this->triggerEvent(
                self::RAPPORT_ACTIVITE__AVIS_MODIFIE__EVENT,
                $rapportAvis,
                []
            );
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", 0, $e);
        }

        return $event;
    }

    /**
     * Supprime en bdd tous les avis sur un rapport d'activité, SANS déclenchement d'événement.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     */
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

    /**
     * @deprecated todo : à déplacer dans une RapportActiviteNotificationFactory
     */
    public function newRapportActiviteAvisNotification(RapportActiviteAvis $rapportActiviteAvis): RapportActiviteAvisNotification
    {
        $notif = new RapportActiviteAvisNotification();
        $notif->setRapportActiviteAvis($rapportActiviteAvis);

        return $notif;
    }

    private function triggerEvent(string $name, $target, array $params = []): RapportActiviteAvisEvent
    {
        $event = new RapportActiviteAvisEvent($name, $target, $params);

        $this->events->triggerEvent($event);

        return $event;
    }
}