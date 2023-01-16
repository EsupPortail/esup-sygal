<?php

namespace RapportActivite\Notification;

use Notification\Notification;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;

class RapportActiviteValidationNotification extends Notification
{
    protected ?string $templatePath = 'rapport-activite/notification/validation';
    private RapportActiviteValidation $rapportActiviteValidation;
    private RapportActiviteAvis $rapportActiviteAvis;

    /**
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     */
    public function setRapportActiviteAvis(RapportActiviteAvis $rapportActiviteAvis): void
    {
        $this->rapportActiviteAvis = $rapportActiviteAvis;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActiviteValidation $rapportActiviteValidation
     */
    public function setRapportActiviteValidation(RapportActiviteValidation $rapportActiviteValidation): void
    {
        $this->rapportActiviteValidation = $rapportActiviteValidation;
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
            'rapportActiviteValidation' => $this->rapportActiviteValidation,
            'rapportActiviteAvis' => $this->rapportActiviteAvis,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}