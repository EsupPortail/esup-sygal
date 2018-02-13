<?php

namespace Application\Service\Notification;

use Application\Entity\Db\Interfaces\TheseAwareTrait;
use Application\Service\Variable\VariableServiceAwareInterface;
use Application\Service\Variable\VariableServiceAwareTrait;
use Zend\View\Model\ViewModel;

abstract class Notification
{
    use VariableServiceAwareTrait;
    use TheseAwareTrait;

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
     * Initialisation, préparation, etc. nécessaires avant de pouvoir envoyer la notification.
     *
     * @param array $context Toutes données utiles
     * @return static
     */
    abstract public function prepare(array $context = []);

    /**
     * Retourne un message d'information destiné à être affiché une fois la notification envoyée.
     *
     * @return string|null
     */
    abstract public function getResultMessage();

    /**
     * Instanciation du modèle de vue utilisé pour le rendu du corps HTML du mail.
     *
     * @return ViewModel
     */
    public function createViewModel()
    {
        $viewModel = new ViewModel();

        $viewModel->setTemplate($this->templatePath);

        $viewModel->setVariable('subject', $this->subject);
        $viewModel->setVariable('to', $this->to);
        $viewModel->setVariable('cc', $this->cc);
        $viewModel->setVariable('bcc', $this->bcc);
        $viewModel->setVariable('these', $this->these);

        $viewModel->setVariables($this->templateVariables, true);

        return $viewModel;
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
        $this->to = $to;

        return $this;
    }

    /**
     * @param string|array $cc
     * @return static
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @param string|array $bcc
     * @return static
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

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
        $this->templateVariables = $templateVariables;

        return $this;
    }
}