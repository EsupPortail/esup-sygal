<?php

namespace Notification;

use Notification\Entity\NotifEntity;
use Zend\View\Model\ViewModel;

/**
 * Classe représentant une notification.
 *
 * @author Unicaen
 */
class Notification
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var NotifEntity
     */
    protected $notifEntity;

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @var array
     */
    protected $templateVariables = [];

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var array
     */
    protected $to;

    /**
     * @var array
     */
    protected $cc;

    /**
     * @var array
     */
    protected $bcc;

    /**
     * @var string[]
     */
    protected $warningMessages = [];

    /**
     * @var string[]
     */
    protected $infoMessages = [];

    /**
     * Notification constructor.
     *
     * @param string|null $code
     */
    public function __construct($code = null)
    {
        $this->code = $code;
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
    private function createTemplateVariables()
    {
        $variables = [];

        $variables['subject'] = $this->getSubject();
        $variables['to'] = $this->getTo();
        $variables['cc'] = $this->getCc();
        $variables['bcc'] = $this->getBcc();

        $variables = array_merge($variables, $this->getTemplateVariables());

        return $variables;
    }

    /**
     * Instanciation du modèle de vue utilisé pour le rendu du corps HTML du mail.
     *
     * @return ViewModel
     */
    public function createViewModel()
    {
        $variables = $this->createTemplateVariables();

        $viewModel = new ViewModel();
        $viewModel->setTemplate($this->templatePath);
        $viewModel->setVariables($variables, true);

        return $viewModel;
    }

    /**
     * @param string $code
     * @return static
     */
    public function setCode($code)
    {
        $this->code = $code;
        $this->notifEntity = null;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param NotifEntity $notifEntity
     */
    public function setNotifEntity(NotifEntity $notifEntity = null)
    {
        $this->notifEntity = $notifEntity;
        $this->code = $notifEntity ? $notifEntity->getCode() : null;
    }

    /**
     * @return NotifEntity
     */
    public function getNotifEntity()
    {
        return $this->notifEntity;
    }

    /**
     * @param string $subject
     * @return static
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string|array $to
     * @return static
     */
    public function setTo($to)
    {
        $this->to = (array) $to;

        return $this;
    }

    /**
     * @param string|array $cc
     * @return static
     */
    public function setCc($cc)
    {
        $this->cc = (array) $cc;

        return $this;
    }

    /**
     * @param string|array $bcc
     * @return static
     */
    public function setBcc($bcc)
    {
        $this->bcc = (array) $bcc;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     * @return Notification
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * @return array
     */
    public function getTemplateVariables()
    {
        return $this->templateVariables;
    }

    /**
     * @param array $templateVariables
     * @return static
     */
    public function setTemplateVariables(array $templateVariables = [])
    {
        $this->templateVariables = array_merge($this->templateVariables, $templateVariables);

        return $this;
    }

    /**
     * @param string|string[] $warningMessages
     * @return self
     */
    protected function setWarningMessages($warningMessages)
    {
        $this->warningMessages = (array) $warningMessages;

        return $this;
    }

    /**
     * @param string|string[] $infoMessages
     * @return self
     */
    protected function setInfoMessages($infoMessages)
    {
        $this->infoMessages = (array) $infoMessages;

        return $this;
    }

    /**
     * Retourne les éventuels messages d'avertissements signalés par cette notification
     * et pouvant être affichés une fois la notification envoyée.
     *
     * @return string[]
     */
    public function getWarningMessages()
    {
        return $this->warningMessages;
    }

    /**
     * Retourne les éventuels messages d'information signalés par cette notification
     * et pouvant être affichés une fois la notification envoyée.
     *
     * @return string[]
     */
    public function getInfoMessages()
    {
        return $this->infoMessages;
    }
}