<?php

namespace Depot\Notification;

use Individu\Entity\Db\Individu;
use Notification\Notification;
use These\Entity\Db\Acteur;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class PasDeMailPresidentJury extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'application/notification/mail/notif-pas-de-mail-president-jury';

    /**
     * @return static
     */
    public function prepare()
    {
        /** @var Individu[] $unknownMails */
        $unknownMails = [];
        $to = $this->emailBdd;
        $cc = null;

        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé à la Maison du doctorat (%s)",
            $to,
        );

        $errorMessage = null;
        if (count($unknownMails)) {
            $temp = current($unknownMails);
            $source = $temp->getSource();
            $errorMessage = sprintf(
                "<strong>NB:</strong> Les directeurs de thèses suivants n'ont pas pu être notifiés " .
                "car leur adresse électronique n'est pas connue dans %s : <br> %s",
                $source,
                implode(',', $unknownMails)
            );
        }

        $this
            ->setSubject("Pas de mail pour le président du jury de la thèse " . $this->these->getId())
            ->setTo($to)
            ->setCc($cc)
            ->setTemplateVariables([
                'message' => $errorMessage,
                'these' => $this->these,
                'president' => $this->president,
            ]);

        $this->setInfoMessages($infoMessage);
        if ($errorMessage) {
            $this->setWarningMessages($errorMessage);
        }

        return $this;
    }

    /**
     * @var string
     */
    protected $emailBdd;

    /**
     * @param mixed $emailBdd
     * @return self
     */
    public function setEmailBdd($emailBdd)
    {
        $this->emailBdd = $emailBdd;
        return $this;
    }

    /**
     * @var Acteur
     */
    protected $president;

    /**
     * @param Acteur $president
     * @return PasDeMailPresidentJury
     */
    public function setPresident(Acteur $president)
    {
        $this->president = $president;
        return $this;
    }



}