<?php

namespace Application\Notification;

use Application\Entity\Db\Interfaces\TheseAwareTrait;
use Notification\Notification;

class ValidationRdvBuNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'application/these/mail/notif-validation-rdv-bu';
    protected $estDevalidation = false;
    protected $notifierDoctorant = false;
    protected $notifierDoctorantImpossibleMessage;

    /**
     * @param array $context
     * @return static
     */
    public function prepare(array $context = [])
    {
        $emailBDD = $this->emailBdd;
        $emailBU = $this->emailBu;

        $doctorant = $this->these->getDoctorant();

        if ($this->estDevalidation) {
            $to = $emailBU;
            $cc = $emailBDD;
        } else {
            if ($this->notifierDoctorant) {
                $emailDoctorant = $doctorant->getEmailPro() ?: $doctorant->getEmail();
                if ($emailDoctorant) {
                    $to = $emailDoctorant;
                    $cc = $emailBDD;
                } else {
                    $this->notifierDoctorantImpossibleMessage =
                        "NB: il n'a pas été possible d'envoyer ce mail à $doctorant car ce doctorant n'a aucune adresse email.";
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
            "Annulation de la validation effectuée à l'issue du rendez-vous avec la BU" :
            "Validation à l'issue du rendez-vous avec la BU"
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
            $this->successMessages[] = sprintf(
                "Un mail de notification vient d'être envoyé à la BU (%s) avec copie au Bureau des Doctorats (%s).",
                $this->getTo(),
                $this->getCc()
            );
        } else {
            if ($this->notifierDoctorant) {
                $this->successMessages[] = sprintf(
                    "Un mail de notification vient d'être envoyé à %s avec copie au Bureau des Doctorats (%s)",
                    $this->these->getDoctorant(),
                    $this->getCc()
                );
            } else {
                $this->successMessages[] = sprintf(
                    "Un mail de notification vient d'être envoyé au Bureau des Doctorats (%s).",
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
     * @param string $emailBdd
     * @return self
     */
    public function setEmailBdd($emailBdd)
    {
        $this->emailBdd = $emailBdd;

        return $this;
    }

    /**
     * @var string
     */
    private $emailBu;

    /**
     * @param string $emailBu
     * @return self
     */
    public function setEmailBu($emailBu)
    {
        $this->emailBu = $emailBu;

        return $this;
    }
}