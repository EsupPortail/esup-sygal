<?php

namespace Depot\Notification;

use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class ValidationRdvBuNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'depot/depot/mail/notif-validation-rdv-bu';
    protected $estDevalidation = false;
    protected $notifierDoctorant = false;
    protected $notifierDoctorantImpossibleMessage;

    /**
     * @return static
     */
    public function prepare()
    {
        $emailBDD = $this->emailBdd;
        $emailBU = $this->emailBu;

        $doctorant = $this->these->getDoctorant();
        $individu = $doctorant->getIndividu();

        if ($this->estDevalidation) {
            $to = $emailBU;
            $cc = $emailBDD;
        } else {
            if ($this->notifierDoctorant) {
                $emailDoctorant = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
                if ($emailDoctorant) {
                    $to = $emailDoctorant;
                    $cc = $emailBDD;
                } else {
                    $this->notifierDoctorantImpossibleMessage =
                        "NB: il n'a pas été possible d'envoyer ce mail à $doctorant car ce doctorant n'a aucune adresse électronique.";
                    $to = $emailBDD;
                    $cc = [];
                }
            } else {
                $to = $emailBDD;
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

    /**
     * @return self
     */
    public function createMessages()
    {
        if ($this->estDevalidation) {
            $this->infoMessages[] = sprintf(
                "Un mail de notification vient d'être envoyé à la bibliothèque universitaire (%s) avec copie à la Maison du doctorat (%s).",
                $this->getTo(),
                $this->getCc()
            );
        } else {
            if ($this->notifierDoctorant) {
                $this->infoMessages[] = sprintf(
                    "Un mail de notification vient d'être envoyé à %s avec copie à la Maison du doctorat (%s)",
                    $this->these->getDoctorant(),
                    $this->getCc()
                );
            } else {
                $this->infoMessages[] = sprintf(
                    "Un mail de notification vient d'être envoyé à la Maison du doctorat (%s).",
                    $this->getTo()
                );
            }
        }

        return $this;
    }

    /**
     * @param bool $estDevalidation
     * @return static
     */
    public function setEstDevalidation($estDevalidation = true)
    {
        $this->estDevalidation = $estDevalidation;

        return $this;
    }

    /**
     * @param bool $notifierDoctorant
     * @return static
     */
    public function setNotifierDoctorant($notifierDoctorant = true)
    {
        $this->notifierDoctorant = $notifierDoctorant;

        return $this;
    }

    /**
     * @var string
     */
    private $emailBdd;

    /**
     * @param string[] $emailBdd
     * @return self
     */
    public function setEmailBdd(array $emailBdd)
    {
        $this->emailBdd = $emailBdd;

        return $this;
    }

    /**
     * @var string
     */
    private $emailBu;

    /**
     * @param string[] $emailBu
     * @return self
     */
    public function setEmailBu(array $emailBu)
    {
        $this->emailBu = $emailBu;

        return $this;
    }
}