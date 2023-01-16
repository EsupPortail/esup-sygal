<?php

namespace Depot\Notification;

use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class ValidationRdvBuNotification extends Notification
{
    use TheseAwareTrait;

    protected ?string $templatePath = 'depot/depot/mail/notif-validation-rdv-bu';
    protected bool $estDevalidation = false;
    protected bool $notifierDoctorant = false;
    protected ?string $notifierDoctorantImpossibleMessage = null;

    public function setEstDevalidation(bool $estDevalidation = true): self
    {
        $this->estDevalidation = $estDevalidation;

        return $this;
    }

    public function setNotifierDoctorant(bool $notifierDoctorant = true): self
    {
        $this->notifierDoctorant = $notifierDoctorant;

        return $this;
    }

    private array $emailsBdd = [];

    /**
     * @param string[] $emailsBdd
     */
    public function setEmailsBdd(array $emailsBdd): self
    {
        $this->emailsBdd = $emailsBdd;

        return $this;
    }

    private array $emailsBu = [];

    /**
     * @param string[] $emailsBu
     */
    public function setEmailsBu(array $emailsBu): self
    {
        $this->emailsBu = $emailsBu;

        return $this;
    }

    public function prepare(): self
    {
        $emailsBDD = $this->emailsBdd;
        $emailsBU = $this->emailsBu;

        $doctorant = $this->these->getDoctorant();
        $individu = $doctorant->getIndividu();

        if ($this->estDevalidation) {
            $to = $emailsBU;
            $cc = $emailsBDD;
        } else {
            if ($this->notifierDoctorant) {
                $emailDoctorant = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
                if ($emailDoctorant) {
                    $to = $emailDoctorant;
                    $cc = $emailsBDD;
                } else {
                    $this->notifierDoctorantImpossibleMessage =
                        "NB: il n'a pas été possible d'envoyer ce mail à $doctorant car ce doctorant n'a aucune adresse électronique.";
                    $to = $emailsBDD;
                    $cc = [];
                }
            } else {
                $to = $emailsBDD;
                $cc = [];
            }
        }
        $this->setTo($to);
        $this->setCc($cc);

        $this->setSubject($this->estDevalidation ?
            "Annulation de la validation effectuée à l'issue du rendez-vous avec la bibliothèque universitaire" :
            "Validation à l'issue du rendez-vous avec la bibliothèque universitaire"
        );

        $this->setTemplateVariables([
            'these' => $this->these,
            'doctorant' => $doctorant,
            'estDevalidation' => $this->estDevalidation,
            'notifierDoctorantImpossibleMessage' => $this->notifierDoctorantImpossibleMessage,
        ]);

        $this->createMessages();

        return $this;
    }

    public function createMessages(): self
    {
        if ($this->estDevalidation) {
            $this->addSuccessMessage(sprintf(
                "Un mail de notification vient d'être envoyé à la bibliothèque universitaire (%s) avec copie à la Maison du doctorat (%s).",
                $this->getTo(),
                $this->getCc()
            ));
        } else {
            if ($this->notifierDoctorant) {
                $this->addSuccessMessage(sprintf(
                    "Un mail de notification vient d'être envoyé à %s avec copie à la Maison du doctorat (%s)",
                    $this->these->getDoctorant(),
                    $this->getCc()
                ));
            } else {
                $this->addSuccessMessage(sprintf(
                    "Un mail de notification vient d'être envoyé à la Maison du doctorat (%s).",
                    $this->getTo()
                ));
            }
        }

        return $this;
    }
}