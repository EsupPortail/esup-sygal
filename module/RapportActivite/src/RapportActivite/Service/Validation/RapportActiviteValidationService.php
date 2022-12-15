<?php

namespace RapportActivite\Service\Validation;

use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Service\BaseService;
use Individu\Service\IndividuServiceAwareInterface;
use Individu\Service\IndividuServiceAwareTrait;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Laminas\EventManager\EventManagerAwareTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Event\Validation\RapportActiviteValidationEvent;
use RapportActivite\Notification\RapportActiviteValidationNotification;
use UnicaenApp\Exception\RuntimeException;

class RapportActiviteValidationService extends BaseService
    implements UserContextServiceAwareInterface, IndividuServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use TypeValidationAwareTrait;
    use EventManagerAwareTrait;

    const RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT = 'RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT';
    const RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT = 'RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT';

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(RapportActiviteValidation::class);
    }

    public function newRapportValidation(RapportActivite $rapportActivite): RapportActiviteValidation
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        return new RapportActiviteValidation(
            $this->typeValidation,
            $rapportActivite,
            $individu);
    }

    /**
     * Enregistre en bdd une validation de rapport d'activité, AVEC déclenchement d'événement.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteValidation $rapportValidation
     * @return \RapportActivite\Event\Validation\RapportActiviteValidationEvent
     */
    public function saveNewRapportValidation(RapportActiviteValidation $rapportValidation): RapportActiviteValidationEvent
    {
        try {
            $this->entityManager->persist($rapportValidation);
            $this->entityManager->flush($rapportValidation);

            // déclenchement d'un événement
            $event = $this->triggerEvent(
                self::RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT,
                $rapportValidation,
                []
            );
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }

        return $event;
    }

    /**
     * @param RapportActivite $rapportActivite
     * @return RapportActiviteValidation|null
     *
     * @todo Utiliser cette méthode et supprimer {@see RapportActivite::getRapportValidation()}
     */
    public function findByRapportActivite(RapportActivite $rapportActivite): ?RapportActiviteValidation
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->join('v.type', 't', Join::WITH, 't = :type')->setParameter('type', $this->typeValidation)
            ->join('v.rapport', 'r', Join::WITH, 'r = :rapport')->setParameter('r', $rapportActivite)
            ->andWhere('v.histoDestruction is null');
        try {
            /** @var RapportActiviteValidation $rapportValidation */
            $rapportValidation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(sprintf(
                "Plusieures validations non historisées de type '%s' trouvées pour le rapport '%s'",
                $this->typeValidation->getCode(),
                $rapportActivite
            ));
        }

        return $rapportValidation;
    }

    /**
     * Supprime en bdd une validation de rapport d'activité, AVEC déclenchement d'événement.
     *
     * @param RapportActiviteValidation $rapportValidation
     * @return \RapportActivite\Event\Validation\RapportActiviteValidationEvent
     */
    public function deleteRapportValidation(RapportActiviteValidation $rapportValidation): RapportActiviteValidationEvent
    {
        $rapportValidation->historiser();
        try {
            $this->getEntityManager()->flush($rapportValidation);

            // déclenchement d'un événement
            $event = $this->triggerEvent(
                self::RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT,
                $rapportValidation,
                []
            );
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }

        return $event;
    }

    /**
     * Supprime en bdd la validation d'un rapport d'activité, SANS déclenchement d'événement.
     *
     * @param RapportActivite $rapportActivite
     */
    public function deleteRapportValidationForRapportActivite(RapportActivite $rapportActivite)
    {
        // NB : il peut y avoir des validations historisées
        foreach ($rapportActivite->getRapportValidations() as $validation) {
            $rapportActivite->removeRapportValidation($validation);
            try {
                $this->getEntityManager()->remove($validation);
                $this->getEntityManager()->flush($validation);
            } catch (ORMException $e) {
                throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
            }
        }
    }

    public function createRapportActiviteValidationNotification(
        RapportActiviteValidation $rapportActiviteValidation,
        RapportActiviteAvis $rapportActiviteAvis): RapportActiviteValidationNotification
    {
        $these = $rapportActiviteAvis->getRapportActivite()->getThese();
        $individu = $these->getDoctorant()->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();

        $notif = new RapportActiviteValidationNotification();
        $notif->setRapportActiviteValidation($rapportActiviteValidation);
        $notif->setRapportActiviteAvis($rapportActiviteAvis);
        $notif->setTo([$email => $these->getDoctorant()->getIndividu()->getNomComplet()]);
        $notif->setCc($these->getDirecteursTheseEmails());
        $notif->setSubject("Rapport d'activité validé");

        return $notif;
    }

    private function triggerEvent(string $name, $target, array $params = []): RapportActiviteValidationEvent
    {
        $event = new RapportActiviteValidationEvent($name, $target, $params);

        $this->events->triggerEvent($event);

        return $event;
    }
}