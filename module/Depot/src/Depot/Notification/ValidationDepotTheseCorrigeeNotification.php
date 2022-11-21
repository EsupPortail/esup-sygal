<?php

namespace Depot\Notification;

use Application\Entity\Db\Utilisateur;
use Individu\Entity\Db\Individu;
use These\Entity\Db\Interfaces\TheseAwareTrait;
use Notification\Notification;

class ValidationDepotTheseCorrigeeNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'application/notification/mail/notif-validation-depot-these-corrigee';

    private ?Utilisateur $destinataire = null;

    /**
     * @return static
     */
    public function prepare()
    {
        /** @var Individu[] $unknownMails */
        $unknownMails = [];
        $to = $this->destinataire ? $this->destinataire->getEmail() : $this->these->getPresidentJuryEmail($unknownMails);
        $cc = $this->emailBdd;

        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé au(x) directeur(s) de thèse (%s) avec copie à la Maison du doctorat (%s)",
            $to,
            $cc
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
            ->setSubject("Validation du dépôt de la thèse corrigée")
            ->setTo($to)
            ->setCc($cc)
            ->setTemplateVariables([
                'message' => $errorMessage,
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

    public function setDestinataire($utilisateur)
    {
        $this->destinataire = $utilisateur;
    }
}