<?php

namespace Depot\Notification;

use Application\Entity\Db\Utilisateur;
use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class ValidationDepotTheseCorrigeeNotification extends Notification
{
    use TheseAwareTrait;

    protected ?string $templatePath = 'application/notification/mail/notif-validation-depot-these-corrigee';

    private ?Utilisateur $destinataire = null;

    /**
     * @return static
     */
    public function prepare(): self
    {
        $to = $this->destinataire ? $this->destinataire->getEmail() : $this->these->getPresidentJuryEmail();
        $cc = $this->emailsBdd;

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé au président du jury (%s) avec copie à la Maison du doctorat (%s)",
            $to,
            $cc
        );

        $this
            ->setSubject("Validation du dépôt de la thèse corrigée")
            ->setTo($to)
            ->setCc($cc);

        $this->addSuccessMessage($successMessage);

        return $this;
    }

    protected array $emailsBdd = [];

    public function setEmailsBdd(array $emailsBdd): self
    {
        $this->emailsBdd = $emailsBdd;

        return $this;
    }

    public function setDestinataire($utilisateur)
    {
        $this->destinataire = $utilisateur;
    }
}