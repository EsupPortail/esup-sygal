<?php

namespace RapportActivite\Notification;

use Notification\Notification;
use RapportActivite\Entity\Db\RapportActiviteAvis;

class RapportActiviteAvisNotification extends Notification
{
    protected $templatePath = 'rapport-activite/notification/avis';
    private RapportActiviteAvis $rapportActiviteAvis;
    private array $messagesByAvisValeurBool = [];

    /**
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     */
    public function setRapportActiviteAvis(RapportActiviteAvis $rapportActiviteAvis): void
    {
        $this->rapportActiviteAvis = $rapportActiviteAvis;
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
        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé aux personnes suivantes : %s",
            implode(', ', array_reduce(array_keys($this->to), function(array $accu, string $key) {
                $accu[] = sprintf('%s (%s)', $this->to[$key], $key);
                return $accu;
            }, []))
        );

        $this->setTemplateVariables([
            'rapportActiviteAvis' => $this->rapportActiviteAvis,
            'messagesByAvisValeurBool' => $this->messagesByAvisValeurBool,
        ]);

        $this->setInfoMessages($infoMessage);

        return $this;
    }
}