<?php

namespace Admission\Event\Validation;

use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\TypeValidation;
use Admission\Event\AdmissionEvent;
use Admission\Event\Operation\AdmissionOperationAbstractEventListener;
use Admission\Service\Operation\AdmissionOperationServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationService;
use Laminas\EventManager\EventManagerInterface;
use Notification\Notification;
use Webmozart\Assert\Assert;

/**
 * @property AdmissionValidation $operationRealisee
 */
class AdmissionValidationEventListener extends AdmissionOperationAbstractEventListener
{
    use AdmissionOperationServiceAwareTrait;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            AdmissionValidationService::class,
            AdmissionValidationService::ADMISSION__VALIDATION_AJOUTE__EVENT,
            [$this, 'onValidationAjoutee'],
            $priority
        );
        $events->getSharedManager()->attach(
            AdmissionValidationService::class,
            AdmissionValidationService::ADMISSION__VALIDATION_SUPPRIME__EVENT,
            [$this, 'onValidationSupprimee'],
            $priority
        );
    }

    protected function initFromEvent(AdmissionEvent $event)
    {
        parent::initFromEvent($event);
        Assert::isInstanceOf($this->operationRealisee, AdmissionValidation::class);
    }

    public function onValidationAjoutee(AdmissionValidationEvent $event)
    {
        $this->initFromEvent($event);
        if (!in_array($this->operationRealisee->getTypeValidation()->getCode(), TypeValidation::CODE_VALIDATIONS_CONVENTION_FORMATION_DOCTORALE)){
            $this->handleNotificationValidationAjoutee();
        }
        $this->handleSuppressionOperationsExistantes();
        $this->handleNotificationOperationAttendue();
    }

    public function onValidationSupprimee(AdmissionValidationEvent $event)
    {
        $this->initFromEvent($event);

        $this->handleNotificationValidationSupprimee();
    }

    private function handleNotificationValidationAjoutee()
    {
        $notif = $this->notificationFactory->createNotificationValidationAjoutee($this->operationRealisee);
        $this->triggerNotification($notif);
    }

    private function handleSuppressionOperationsExistantes()
    {
        // suppression de toutes les opérations suivantes.
        $operation = $this->operationRealisee;
        while ($operation = $this->admissionOperationRule->findFollowingOperation($operation)) {
            if ($operation->getId() === null) {
                // opération non réalisée (çàd rien en bdd),
                continue;
            }
            $this->admissionOperationService->deleteOperation($operation);
        }
    }

    private function handleNotificationValidationSupprimee()
    {
        $notif = $this->notificationFactory->createNotificationValidationSupprimee($this->operationRealisee);
        $notif->setTemplateVariables(['messages' => $this->event->getMessages()]);
        $this->triggerNotification($notif);
    }
}
