<?php

namespace RapportActivite\Notification;

use Notification\Notification;
use RapportActivite\Entity\RapportActiviteOperationInterface;

class RapportActiviteOperationAttenduNotification extends Notification
{
    protected ?string $templatePath = 'rapport-activite/notification/operation-attendue';
    private RapportActiviteOperationInterface $operationAttendue;
    private array $anomalies = [];

    public function setOperationAttendue(RapportActiviteOperationInterface $operationAttendue): void
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