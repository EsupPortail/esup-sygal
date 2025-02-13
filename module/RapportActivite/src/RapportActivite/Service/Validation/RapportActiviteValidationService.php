<?php

namespace RapportActivite\Service\Validation;

use Validation\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Application\Service\BaseService;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Validation\Service\ValidationThese\ValidationTheseServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Service\IndividuServiceAwareInterface;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\EventManager\EventManagerAwareTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Event\Validation\RapportActiviteValidationEvent;
use UnicaenApp\Exception\RuntimeException;

class RapportActiviteValidationService extends BaseService
    implements UserContextServiceAwareInterface, IndividuServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationTheseServiceAwareTrait;
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

    public function newRapportValidation(RapportActivite $rapportActivite, TypeValidation $typeValidation): RapportActiviteValidation
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        return new RapportActiviteValidation(
            $typeValidation,
            $rapportActivite,
            $individu);
    }

    /**
     * Enregistre en bdd une validation de rapport d'activité.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteValidation $rapportValidation
     */
    public function saveNewRapportValidation(RapportActiviteValidation $rapportValidation)
    {
        try {
            $this->entityManager->persist($rapportValidation);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }
    }

    public function triggerEventValidationAjoutee(RapportActiviteValidation $rapportValidation, array $params = []): RapportActiviteValidationEvent
    {
        return $this->triggerEvent(
            self::RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT,
            $rapportValidation,
            $params
        );
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     * @param \Validation\Entity\Db\TypeValidation $type
     * @return \RapportActivite\Entity\Db\RapportActiviteValidation|null
     *
     * @deprecated Remplacé par l'utilisation d'un fetch du rapport avec les relations vers les entités liées
     */
    public function findByRapportActiviteAndType(RapportActivite $rapportActivite, TypeValidation $type): ?RapportActiviteValidation
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->join('v.typeValidation', 't', Join::WITH, 't = :type')->setParameter('type', $type)
            ->join('v.rapport', 'r', Join::WITH, 'r = :rapport')->setParameter('rapport', $rapportActivite)
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
     * Historise une validation de rapport d'activité.
     *
     * @param RapportActiviteValidation $rapportValidation
     */
    public function deleteRapportValidation(RapportActiviteValidation $rapportValidation)
    {
        $rapportValidation->historiser();
        try {
            $this->getEntityManager()->flush($rapportValidation);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }
    }

    public function triggerEventValidationSupprimee(RapportActiviteValidation $rapportValidation, array $params = []): RapportActiviteValidationEvent
    {
        return $this->triggerEvent(
            self::RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT,
            $rapportValidation,
            $params
        );
    }

    /**
     * Supprime physiquement en bdd la validation d'un rapport d'activité.
     *
     * @param RapportActivite $rapportActivite
     */
    public function deleteRapportValidationForRapportActivite(RapportActivite $rapportActivite)
    {
        // NB : inclusion des validations historisées
        foreach ($rapportActivite->getRapportValidations(true) as $validation) {
            $rapportActivite->removeRapportValidation($validation);
            try {
                $this->getEntityManager()->remove($validation);
                $this->getEntityManager()->flush($validation);
            } catch (ORMException $e) {
                throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
            }
        }
    }

    private function triggerEvent(string $name, $target, array $params = []): RapportActiviteValidationEvent
    {
        $messages = [];
        if (isset($params['messages'])) {
            $messages = $params['messages'];
            unset($params['messages']);
        }

        $event = new RapportActiviteValidationEvent($name, $target, $params);
        if ($messages) {
            $event->addMessages($messages);
        }

        $this->events->triggerEvent($event);

        return $event;
    }
}