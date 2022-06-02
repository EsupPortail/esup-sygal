<?php

namespace RapportActivite\Notification;

use Notification\Notification;
use RapportActivite\Entity\Db\RapportActivite;

class RapportActiviteSupprimeNotification extends Notification
{
    protected $templatePath = 'rapport-activite/notification/rapport-supprime';
    private RapportActivite $rapportActivite;

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     */
    public function setRapportActivite(RapportActivite $rapportActivite): void
    {
        $this->rapportActivite = $rapportActivite;
    }

    /**
     * @return self
     */
    public function prepare(): self
    {
        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé aux personnes suivantes : %s",
            implode(', ', array_reduce(array_keys($this->to), function(array $accu, string $key) {
                $accu[] = sprintf('%s (%s)', $this->to[$key], $key);
                return $accu;
            }, []))
        );

        $this->setTemplateVariables([
            'rapportActivite' => $this->rapportActivite,
        ]);

        $this->setInfoMessages($infoMessage);

        return $this;
    }
}