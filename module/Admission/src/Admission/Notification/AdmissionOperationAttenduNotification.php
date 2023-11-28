<?php

namespace Admission\Notification;

use Admission\Entity\Db\AdmissionOperationInterface;
use Notification\Notification;

class AdmissionOperationAttenduNotification extends Notification
{
    protected ?string $templatePath = 'admission/notification/operation-attendue';
    private AdmissionOperationInterface $operationAttendue;
    private array $anomalies = [];

    public function setOperationAttendue(AdmissionOperationInterface $operationAttendue): void
    {
        $this->operationAttendue = $operationAttendue;
    }

    public function setAnomalies(array $anomalies)
    {
        $this->anomalies = $anomalies;
    }

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
            'operationAttendue' => $this->operationAttendue,
            'anomalies' => $this->anomalies,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}