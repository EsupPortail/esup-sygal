<?php

namespace Admission\Notification;

use Admission\Entity\Db\AdmissionAvis;
use Notification\Notification;

class AdmissionAvisNotification extends Notification
{
    protected ?string $templatePath = 'rapport-activite/notification/avis';
    private AdmissionAvis $admissionAvis;
    private array $messagesByAvisValeurBool = [];

    /**
     * @param AdmissionAvis $admissionAvis
     */
    public function setAdmissionAvis(AdmissionAvis $admissionAvis): void
    {
        $this->admissionAvis = $admissionAvis;
    }

    /**
     * @param array $messagesByAvisValeurBool
     */
    public function setMessagesByAvisValeurBool(array $messagesByAvisValeurBool): void
    {
        $this->messagesByAvisValeurBool = $messagesByAvisValeurBool;
    }

    /**
     * @return self
     */
    public function prepare(): self
    {
        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé aux personnes suivantes : %s",
            implode(', ', array_reduce(array_keys($this->to), function(array $accu, string $key) {
                $accu[] = sprintf('%s (%s)', $this->to[$key], $key);
                return $accu;
            }, []))
        );

        $this->setTemplateVariables([
            'admissionAvis' => $this->admissionAvis,
            'messagesByAvisValeurBool' => $this->messagesByAvisValeurBool,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}