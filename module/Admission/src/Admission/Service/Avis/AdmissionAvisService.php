<?php

namespace Admission\Service\Avis;

use Application\Service\BaseService;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Laminas\EventManager\EventManagerAwareTrait;
use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Event\Avis\AdmissionAvisEvent;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class AdmissionAvisService extends BaseService
{
    use AvisServiceAwareTrait;
    use EventManagerAwareTrait;

    public const ADMISSION__AVIS_AJOUTE__EVENT = 'ADMISSION__AVIS_AJOUTE__EVENT';
    public const ADMISSION__AVIS_MODIFIE__EVENT = 'ADMISSION__AVIS_MODIFIE__EVENT';
    public const ADMISSION__AVIS_SUPPRIME__EVENT = 'ADMISSION__AVIS_SUPPRIME__EVENT';

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(AdmissionAvis::class);
    }

    /**
     * Historise en bdd un avis sur un dossier d'admission.
     *
     * @param AdmissionAvis $admissionAvis
     */
    public function deleteAdmissionAvis(AdmissionAvis $admissionAvis)
    {
        $this->entityManager->beginTransaction();
        $admissionAvis->historiser();
        try {
            $this->getEntityManager()->flush($admissionAvis);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'historisation de l'avis, rollback!", 0, $e);
        }
    }

    public function triggerEventAvisSupprime(AdmissionAvis $admissionAvis, array $params = []): AdmissionAvisEvent
    {
        return $this->triggerEvent(
            self::ADMISSION__AVIS_SUPPRIME__EVENT,
            $admissionAvis,
            $params
        );
    }

    public function newAdmissionAvis(Admission $admission): AdmissionAvis
    {
        $admissionAvis = new AdmissionAvis();
        $admissionAvis
            ->setAdmission($admission);

        return $admissionAvis;

    }

    /**
     * Enregistre en bdd un nouvel avis sur un dossier d'admission.
     *
     * @param AdmissionAvis $admissionAvis
     */
    public function saveNewAdmissionAvis(AdmissionAvis $admissionAvis)
    {
        $this->entityManager->beginTransaction();

        $admission = $admissionAvis->getAdmission();
        $admission->addAdmissionAvis($admissionAvis);

        try {
            $this->avisService->saveAvis($admissionAvis->getAvis());

            $this->entityManager->persist($admissionAvis);
            $this->entityManager->flush($admissionAvis);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", 0, $e);
        }
    }

    public function triggerEventAvisAjoute(AdmissionAvis $admissionAvis, array $params = []): AdmissionAvisEvent
    {
        return $this->triggerEvent(
            self::ADMISSION__AVIS_AJOUTE__EVENT,
            $admissionAvis,
            $params
        );
    }

    /**
     * Met à jour en bdd un avis sur un dossier d'admission.
     *
     * @param AdmissionAvis $admissionAvis
     */
    public function updateAdmissionAvis(AdmissionAvis $admissionAvis)
    {
        $this->entityManager->beginTransaction();

        try {
            $this->avisService->saveAvis($admissionAvis->getAvis());

            $admissionAvis->setHistoModification(new DateTime());
            $admissionAvis->setHistoModificateur();

            $this->entityManager->flush($admissionAvis);
            $this->entityManager->commit();
        } catch (ORMException $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", 0, $e);
        }
    }

    public function triggerEventAvisModifie(AdmissionAvis $admissionAvis, array $params = []): AdmissionAvisEvent
    {
        return $this->triggerEvent(
            self::ADMISSION__AVIS_MODIFIE__EVENT,
            $admissionAvis,
            $params
        );
    }

    /**
     * Supprime en bdd tous les avis sur un dossier d'admission.
     *
     * @param Admission $admission
     */
    public function deleteAllAvisForAdmission(Admission $admission)
    {
        try {
            foreach ($admission->getAdmissionAvis(true) as $admissionAvis) {
                $admission->removeAdmissionAvis($admissionAvis);
                $this->entityManager->remove($admissionAvis);
                $this->entityManager->flush($admissionAvis);
            }
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
        }
    }

    private function triggerEvent(string $name, $target, array $params = []): AdmissionAvisEvent
    {
        $messages = [];
        if (isset($params['messages'])) {
            $messages = $params['messages'];
            unset($params['messages']);
        }

        $event = new AdmissionAvisEvent($name, $target, $params);
        if ($messages) {
            $event->addMessages($messages);
        }

        $this->events->triggerEvent($event);

        return $event;
    }
}