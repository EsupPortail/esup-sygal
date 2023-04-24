<?php

namespace RapportActivite\Event\Validation;

use Laminas\EventManager\EventManagerInterface;
use Notification\Notification;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Event\Operation\RapportActiviteOperationAbstractEventListener;
use RapportActivite\Event\RapportActiviteEvent;
use RapportActivite\Service\Operation\RapportActiviteOperationServiceAwareTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use Webmozart\Assert\Assert;

/**
 * @property \RapportActivite\Entity\Db\RapportActiviteValidation $operationRealisee
 */
class RapportActiviteValidationEventListener extends RapportActiviteOperationAbstractEventListener
{
    use RapportActiviteOperationServiceAwareTrait;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            RapportActiviteValidationService::class,
            RapportActiviteValidationService::RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT,
            [$this, 'onValidationAjoutee'],
            $priority
        );
        $events->getSharedManager()->attach(
            RapportActiviteValidationService::class,
            RapportActiviteValidationService::RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT,
            [$this, 'onValidationSupprimee'],
            $priority
        );
    }

    protected function initFromEvent(RapportActiviteEvent $event)
    {
        parent::initFromEvent($event);
        Assert::isInstanceOf($this->operationRealisee, RapportActiviteValidation::class);
    }

    public function onValidationAjoutee(RapportActiviteValidationEvent $event)
    {
        $this->initFromEvent($event);

        $this->handleNotificationValidationAjoutee();
        $this->handleSuppressionOperationsExistantes();
        $this->handleNotificationOperationAttendue();
    }

    public function onValidationSupprimee(RapportActiviteValidationEvent $event)
    {
        $this->initFromEvent($event);

        $this->handleNotificationValidationSupprimee();
    }

    private function handleNotificationValidationAjoutee()
    {
        $notif = $this->rapportActiviteNotificationFactory->createNotificationValidationAjoutee($this->operationRealisee);
        $this->triggerNotification($notif);
    }

    private function handleSuppressionOperationsExistantes()
    {
        // suppression de toutes les opérations suivantes.
        $operation = $this->operationRealisee;
        while ($operation = $this->rapportActiviteOperationRule->findFollowingOperation($operation)) {
            if ($operation->getId() === null) {
                // opération non réalisée (çàd rien en bdd),
                continue;
            }
            $this->rapportActiviteOperationService->deleteOperation($operation);
        }
    }

    private function handleNotificationValidationSupprimee()
    {
        $notif = $this->rapportActiviteNotificationFactory->createNotificationValidationSupprimee($this->operationRealisee);
        $notif->setTemplateVariables(['messages' => $this->event->getMessages()]);
        $this->triggerNotification($notif);
    }

    private function triggerNotification(Notification $notification)
    {
        $result = $this->notifierService->trigger($notification);

        $messages['info'] = ($result->getSuccessMessages()[0] ?? null);
        $messages['warning'] = ($result->getErrorMessages()[0] ?? null);
        $this->event->setMessages(array_filter($messages));
    }
}
