<?php

namespace Admission\Notification;

use Admission\Entity\Db\AdmissionAvis;
use Notification\Notification;

class AdmissionAvisNotification extends Notification
{
    private AdmissionAvis $admissionAvis;
    private array $anomalies = [];

    public function setAnomalies(array $anomalies)
    {
        $this->anomalies = $anomalies;
    }

    public function getAnomalies()
    {
        return implode('<br>', $this->anomalies);
    }

    /**
     * @param AdmissionAvis $admissionAvis
     */
    public function setAdmissionAvis(AdmissionAvis $admissionAvis): void
    {
        $this->admissionAvis = $admissionAvis;
    }

    public function getAdmissionAvis(): AdmissionAvis
    {
        return $this->admissionAvis;
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
            'anomalies' => $this->anomalies,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}