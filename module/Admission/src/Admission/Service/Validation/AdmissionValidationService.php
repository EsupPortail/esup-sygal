<?php

namespace Admission\Service\Validation;

use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\Repository\AdmissionValidationRepository;
use Admission\Entity\Db\TypeValidation;
use Admission\Event\Validation\AdmissionValidationEvent;
use Application\Service\BaseService;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Laminas\EventManager\EventManagerAwareTrait;
use Admission\Entity\Db\Admission;
use UnicaenApp\Exception\RuntimeException;

class AdmissionValidationService extends BaseService
{
    use UserContextServiceAwareTrait;
    use EventManagerAwareTrait;

    const ADMISSION__VALIDATION_AJOUTE__EVENT = 'ADMISSION__VALIDATION_AJOUTE__EVENT';
    const ADMISSION__VALIDATION_SUPPRIME__EVENT = 'ADMISSION__VALIDATION_SUPPRIME__EVENT';

    /**
     * @return AdmissionValidationRepository
     * @throws NotSupported
     */
    public function getRepository(): AdmissionValidationRepository
    {
        /** @var AdmissionValidationRepository $repo */
        $repo = $this->entityManager->getRepository(AdmissionValidation::class);

        return $repo;
    }

    public function newAdmissionValidation(Admission $admission, TypeValidation $typeValidation): AdmissionValidation
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        return new AdmissionValidation(
            $typeValidation,
            $admission,
            $individu);
    }

    /**
     * Enregistre en bdd une validation de dossier d'admission.
     *
     * @param AdmissionValidation $admissionValidation
     */
    public function saveNewAdmissionValidation(AdmissionValidation $admissionValidation)
    {
        try {
            $this->entityManager->persist($admissionValidation);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }
    }

    public function triggerEventValidationAjoutee(AdmissionValidation $admissionValidation, array $params = []): AdmissionValidationEvent
    {
        return $this->triggerEvent(
            self::ADMISSION__VALIDATION_AJOUTE__EVENT,
            $admissionValidation,
            $params
        );
    }

    /**
     * Historise une validation d'un dossier d'admission.
     *
     * @param AdmissionValidation $admissionValidation
     */
    public function deleteAdmissionValidation(AdmissionValidation $admissionValidation)
    {
        $admissionValidation->historiser();
        try {
            $this->getEntityManager()->flush($admissionValidation);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }
    }

    public function triggerEventValidationSupprimee(AdmissionValidation $admissionValidation, array $params = []): AdmissionValidationEvent
    {
        return $this->triggerEvent(
            self::ADMISSION__VALIDATION_SUPPRIME__EVENT,
            $admissionValidation,
            $params
        );
    }

    /**
     * Supprime physiquement en bdd la validation d'un dossier d'admission.
     *
     * @param Admission $admission
     */
    public function deleteValidationForAdmission(Admission $admission)
    {
        // NB : inclusion des validations historisées
        foreach ($admission->getAdmissionValidations(true) as $validation) {
            $admission->removeAdmissionValidation($validation);
            try {
                $this->entityManager->remove($validation);
                $this->entityManager->flush($validation);
            } catch (ORMException $e) {
                throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
            }
        }
    }

    private function triggerEvent(string $name, $target, array $params = []): AdmissionValidationEvent
    {
        $messages = [];
        if (isset($params['messages'])) {
            $messages = $params['messages'];
            unset($params['messages']);
        }

        $event = new AdmissionValidationEvent($name, $target, $params);
        if ($messages) {
            $event->addMessages($messages);
        }

        $this->events->triggerEvent($event);

        return $event;
    }
}