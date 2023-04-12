<?php

namespace Notification;

use DateTime;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;

/**
 * Classe représentant le résultat d'un envoi de notification.
 *
 * @author Unicaen
 */
class NotificationResult
{
    protected bool $isSuccess;
    protected Notification $notification;
    protected array $successMessages = [];
    protected array $errorMessages = [];
    protected ?DateTime $sendDate = null;

    /**
     * Notification constructor.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function setIsSuccess(bool $isSuccess = true): self
    {
        $this->isSuccess = $isSuccess;
        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * Spécifie les éventuels messages d'information signalés par cette notification
     * et pouvant être affichés une fois la notification envoyée.
     *
     * @param string[] $successMessages
     * @return self
     */
    public function setSuccessMessages(array $successMessages): self
    {
        $this->successMessages = $successMessages;

        return $this;
    }

    /**
     * Retourne les éventuels messages d'information signalés par cette notification
     * et pouvant être affichés une fois la notification envoyée.
     *
     * @return string[]
     */
    public function getSuccessMessages(): array
    {
        return $this->successMessages;
    }

    /**
     * Spécifie les éventuels messages d'avertissement signalés par cette notification
     * et pouvant être affichés une fois la notification envoyée.
     *
     * @param string[] $errorMessages
     * @return self
     */
    public function setErrorMessages(array $errorMessages): self
    {
        $this->errorMessages = $errorMessages;

        return $this;
    }

    /**
     * Retourne les éventuels messages des erreurs/problèmes rencontrés par cette notification
     * et pouvant être affichés une fois la notification envoyée.
     *
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * Renseigne la date d'envoi théorique de cette notification.
     */
    public function setSendDate(DateTime $sendDate): self
    {
        $this->sendDate = $sendDate;

        return $this;
    }

    /**
     * Retourne la date d'envoi théorique de cette notification.
     */
    public function getSendDate(): DateTime
    {
        return $this->sendDate;
    }

    /**
     * Alimente un {@see \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger} avec les éventuelles messages existants
     * dans ce résultat de notification.
     */
    public function feedFlashMessenger(FlashMessenger $flashMessenger, string $namespacePrefix = '')
    {
        foreach ($this->getSuccessMessages() as $successMessage) {
            $flashMessenger->addMessage($successMessage, $namespacePrefix . 'success');
        }
        foreach ($this->getErrorMessages() as $failureMessage) {
            $flashMessenger->addMessage($failureMessage, $namespacePrefix . 'danger');
        }
    }
}