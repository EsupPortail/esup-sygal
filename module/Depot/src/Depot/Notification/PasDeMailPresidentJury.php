<?php

namespace Depot\Notification;

use Notification\Notification;
use Acteur\Entity\Db\ActeurThese;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class PasDeMailPresidentJury extends Notification
{
    use TheseAwareTrait;

    protected ?string $templatePath = 'application/notification/mail/notif-pas-de-mail-president-jury';

    /**
     * @return static
     */
    public function prepare()
    {
        $to = $this->emailsBdd;
        $cc = null;

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé à la Maison du doctorat (%s)",
            $to,
        );

        $this
            ->setSubject("Pas de mail pour le président du jury de la thèse " . $this->these->getId())
            ->setTo($to)
            ->setCc($cc)
            ->setTemplateVariables([
                'these' => $this->these,
                'president' => $this->president,
            ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }

    protected array $emailsBdd = [];

    public function setEmailsBdd(array $emailsBdd): self
    {
        $this->emailsBdd = $emailsBdd;
        return $this;
    }

    /**
     * @var ActeurThese
     */
    protected $president;

    /**
     * @param ActeurThese $president
     * @return PasDeMailPresidentJury
     */
    public function setPresident(ActeurThese $president)
    {
        $this->president = $president;
        return $this;
    }



}