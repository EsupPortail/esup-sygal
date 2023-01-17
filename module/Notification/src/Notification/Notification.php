<?php

namespace Notification;

use Laminas\View\Model\ViewModel;
use Notification\Entity\NotifEntity;

/**
 * Classe représentant une notification.
 *
 * @author Unicaen
 */
class Notification
{
    protected ?string $code = null;
    protected ?NotifEntity $notifEntity = null;
    protected ?string $templatePath = null;
    protected array $templateVariables = [];
    protected string $subject;
    protected ?string $body = null;
    protected array $to = [];
    protected array $cc = [];
    protected array $bcc = [];
    protected ?string $toLabel = null;
    protected ?string $ccLabel = null;
    protected ?string $bccLabel = null;

    /**
     * @var string[]
     */
    protected array $errorMessages = [];

    /**
     * @var string[]
     */
    protected array $successMessages = [];

    /**
     * @var string[]
     */
    protected array $informationMessages = [];

    /**
     * @var \DateTime
     * @deprecated
     */
    protected $sendDate;

    /**
     * Notification constructor.
     */
    public function __construct(?string $code = null)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->subject . " => " . ($this->to ? implode(", ", $this->to) : "Aucun destinataire") . ".";
    }

    /**
     * Éventuelle initialisation, préparation, etc. nécessaires avant de pouvoir envoyer la notification.
     */
    public function prepare()
    {
        $this->mergeFromNotifEntity();
    }

    private function mergeFromNotifEntity()
    {
        $to = $this->getTo();

        if ($this->notifEntity !== null) {
            $toFromEntity = [];
            if ($recipients = $this->notifEntity->getRecipients()) {
                $toFromEntity = array_map('trim', explode(',', $recipients));
            }
            $to = array_merge($toFromEntity, $to);
        }

        $this->setTo($to);
    }

    /**
     * @return array
     */
    private function createTemplateVariables(): array
    {
        $variables = [];

        $variables['subject'] = $this->getSubject();
        $variables['to'] = $this->getTo();
        $variables['cc'] = $this->getCc();
        $variables['bcc'] = $this->getBcc();

        return array_merge($variables, $this->getTemplateVariables());
    }

    /**
     * Instanciation du modèle de vue utilisé pour le rendu du corps HTML du mail.
     *
     * @return ViewModel
     */
    public function createViewModel(): ViewModel
    {
        $variables = $this->createTemplateVariables();

        $viewModel = new ViewModel();
        $viewModel->setTemplate($this->templatePath);
        $viewModel->setVariables($variables, true);

        return $viewModel;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        $this->notifEntity = null;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setNotifEntity(?NotifEntity $notifEntity = null)
    {
        $this->notifEntity = $notifEntity;
        $this->code = $notifEntity ? $notifEntity->getCode() : null;
    }

    public function getNotifEntity(): ?NotifEntity
    {
        return $this->notifEntity;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param string|array $to
     */
    public function setTo($to): self
    {
        $this->to = (array) $to;

        return $this;
    }

    /**
     * @param string|array $cc
     */
    public function setCc($cc): self
    {
        $this->cc = (array) $cc;

        return $this;
    }

    /**
     * @param string|array $bcc
     */
    public function setBcc($bcc): self
    {
        $this->bcc = (array) $bcc;

        return $this;
    }

    /**
     * Fournit un libellé explicitant qui sont les destinataires, ex : "Direction de thèse et Maison du doctorat".
     */
    public function setToLabel(string $toLabel): self
    {
        $this->toLabel = $toLabel;

        return $this;
    }

    /**
     * Fournit un libellé explicitant qui sont les destinataires, ex : "Direction de thèse et Maison du doctorat".
     */
    public function setCcLabel(string $ccLabel): self
    {
        $this->ccLabel = $ccLabel;

        return $this;
    }

    /**
     * Fournit un libellé explicitant qui sont les destinataires, ex : "Direction de thèse et Maison du doctorat".
     */
    public function setBccLabel(string $bccLabel): self
    {
        $this->bccLabel = $bccLabel;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function getTemplatePath(): ?string
    {
        return $this->templatePath;
    }

    public function setTemplatePath(string $templatePath): self
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplateVariable(string $name)
    {
        return $this->templateVariables[$name] ?? null;
    }

    public function getTemplateVariables(): array
    {
        return $this->templateVariables;
    }

    public function setTemplateVariables(array $templateVariables = []): Notification
    {
        $this->templateVariables = array_merge($this->templateVariables, $templateVariables);

        return $this;
    }

    /**
     * Spécifie les messages exprimant le succès de l'envoi de cette notification.
     * 
     * Ces messages seront collectés et disponibles dans le {@see \Notification\NotificationResult} à l'issu de l'envoi
     * de la notification.
     *
     * @param string[] $successMessages
     * @return \Notification\Notification
     */
    public function setSuccessMessages(array $successMessages): self
    {
        $this->successMessages = $successMessages;

        return $this;
    }

    /**
     * Ajoute un message exprimant le succès de l'envoi de cette notification.
     * 
     * Ces messages seront collectés et disponibles dans le {@see \Notification\NotificationResult} à l'issu de l'envoi
     * de la notification.
     *
     * @param string $successMessage
     * @return \Notification\Notification
     */
    public function addSuccessMessage(string $successMessage): self
    {
        $this->successMessages[] = $successMessage;

        return $this;
    }

    /**
     * Retourne les messages à collecter en cas de succès de l'envoi de cette notification.
     * Ces messages n'ont de sens que si l'envoi de la notification a réussi.
     * Si vous ne savez pas ce que vous faites, utiliser {@see \Notification\NotificationResult::getSuccessMessages()}.
     *
     * @return string[]
     */
    public function getSuccessMessages(): array
    {
        return $this->successMessages;
    }

    /**
     * Spécifie les messages d'erreur concernant les problèmes rencontrés par cette notification INDÉPENDAMMENT
     * de l'envoi de celle-ci.
     * Exemple d'erreur rencontrable : l'adresse mail d'un des destinataires n'a pas été trouvée.
     *
     * NB : L'existence de tels messages d'erreurs ne signifie pas l'impossibilité d'envoyer la notification.
     * En cas d'erreur majeure empêchant l'envoi de la notification, il convient de lancer une
     * {@see \Notification\Exception\RuntimeException}.
     *
     * @param string[] $errorMessages
     */
    public function setErrorMessages(array $errorMessages): self
    {
        $this->errorMessages = $errorMessages;

        return $this;
    }

    /**
     * Ajoute un message d'erreur concernant un problème rencontré par cette notification INDÉPENDAMMENT
     * de l'envoi de celle-ci.
     * Exemple d'erreur rencontrable : l'adresse mail d'un des destinataires n'a pas été trouvée.
     *
     * NB : L'existence de tels messages d'erreurs ne signifie pas l'impossibilité d'envoyer la notification.
     * En cas d'erreur majeure empêchant l'envoi de la notification, il convient de lancer une
     * {@see \Notification\Exception\RuntimeException}.
     *
     * @param string $errorMessage
     * @return \Notification\Notification
     */
    public function addErrorMessage(string $errorMessage): self
    {
        $this->errorMessages[] = $errorMessage;

        return $this;
    }

    /**
     * Retourne les éventuels messages d'avertissements rencontrés par cette notification *INDÉPENDAMMENT de son envoi*
     * Exemple d'erreur rencontrable : l'adresse mail d'un des destinataires n'a pas été trouvée.
     *
     * NB : L'existence de tels messages d'erreurs ne signifie pas l'impossibilité d'envoyer la notification.
     * En cas d'erreur majeure empêchant l'envoi de la notification, il convient de lancer une
     * {@see \Notification\Exception\RuntimeException}.
     *
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * Retourne la date d'envoi éventuelle de cette notification.
     *
     * @return \DateTime
     * @deprecated Utiliser {@see \Notification\NotificationResult::getSendDate()}
     */
    public function getSendDate()
    {
        return $this->sendDate;
    }
}