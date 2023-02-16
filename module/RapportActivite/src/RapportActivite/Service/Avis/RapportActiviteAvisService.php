<?php

namespace RapportActivite\Service\Avis;

use Application\Service\BaseService;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use Laminas\EventManager\EventManagerAwareTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Event\Avis\RapportActiviteAvisEvent;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class RapportActiviteAvisService extends BaseService
{
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
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     * @param \UnicaenAvis\Entity\Db\AvisType|string $avisType
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function findRapportAvisByRapportAndAvisType(RapportActivite $rapportActivite, $avisType): ?RapportActiviteAvis
    {
        $code = $avisType instanceof AvisType ? $avisType->getCode() : $avisType;

        $qb = $this->getRepository()->createQueryBuilder('ra')
            ->addSelect('r, a, at')
            ->join('ra.rapport', 'r', Join::WITH, 'r = :rapport')->setParameter('rapport', $rapportActivite)
            ->join('ra.avis', 'a')
            ->join('a.avisType', 'at')
            ->andWhere('at.code = :code')->setParameter('code', $code)
            ->andWhereNotHistorise();

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Impossible mais vrai !");
        }
    }

    /**
     * Supprime en bdd un avis sur un rapport d'activité.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportAvis
     */
    public function deleteRapportAvis(RapportActiviteAvis $rapportAvis)
    {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->remove($rapportAvis); // l'Avis sera supprimé en cascade (cf. mapping)
            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression de l'avis, rollback!", 0, $e);
        }
    }

    public function triggerEventAvisSupprime(RapportActiviteAvis $rapportAvis, array $params = []): RapportActiviteAvisEvent
    {
        return $this->triggerEvent(
            self::RAPPORT_ACTIVITE__AVIS_SUPPRIME__EVENT,
            $rapportAvis,
            $params
        );
    }

    public function newRapportAvis(RapportActivite $rapportActivite): RapportActiviteAvis
    {
        $rapportActiviteAvis = new RapportActiviteAvis();
        $rapportActiviteAvis
            ->setRapportActivite($rapportActivite);

        return $rapportActiviteAvis;

    }

    /**
     * Enregistre en bdd un nouvel avis sur un rapport d'activité.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportAvis
     */
    public function saveNewRapportAvis(RapportActiviteAvis $rapportAvis)
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

    public function triggerEventAvisAjoute(RapportActiviteAvis $rapportAvis, array $params = []): RapportActiviteAvisEvent
    {
        return $this->triggerEvent(
            self::RAPPORT_ACTIVITE__AVIS_AJOUTE__EVENT,
            $rapportAvis,
            $params
        );
    }

    /**
     * Met à jour en bdd un avis sur un rapport d'activité.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportAvis
     */
    public function updateRapportAvis(RapportActiviteAvis $rapportAvis)
    {
        $this->entityManager->beginTransaction();

        try {
            $this->avisService->saveAvis($rapportAvis->getAvis());

            $rapportAvis->setHistoModification(new DateTime());
            $rapportAvis->setHistoModificateur();

            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();
        } catch (ORMException $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", 0, $e);
        }
    }

    public function triggerEventAvisModifie(RapportActiviteAvis $rapportAvis, array $params = []): RapportActiviteAvisEvent
    {
        return $this->triggerEvent(
            self::RAPPORT_ACTIVITE__AVIS_MODIFIE__EVENT,
            $rapportAvis,
            $params
        );
    }

    /**
     * Supprime en bdd tous les avis sur un rapport d'activité.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     */
    public function deleteAllAvisForRapportActivite(RapportActivite $rapportActivite)
    {
        try {
            foreach ($rapportActivite->getRapportAvis() as $rapportAvis) {
                $rapportActivite->removeRapportAvis($rapportAvis);
                $this->entityManager->remove($rapportAvis);
                $this->entityManager->flush($rapportAvis);
            }
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
        }
    }

    private function triggerEvent(string $name, $target, array $params = []): RapportActiviteAvisEvent
    {
        $messages = [];
        if (isset($params['messages'])) {
            $messages = $params['messages'];
            unset($params['messages']);
        }

        $event = new RapportActiviteAvisEvent($name, $target, $params);
        if ($messages) {
            $event->addMessages($messages);
        }

        $this->events->triggerEvent($event);

        return $event;
    }
}