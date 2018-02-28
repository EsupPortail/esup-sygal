<?php

namespace Application\Notification;

use Application\Entity\Db\Variable;
use Application\Service\Notification\Notification;

class ValidationRdvBuNotification extends Notification
{
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
        $variables = $this->variableService->getRepository()->findByCodeAndThese([
            Variable::CODE_EMAIL_BDD,
            Variable::CODE_EMAIL_BU,
        ], $this->getThese());
        $emailBDD = $variables[Variable::CODE_EMAIL_BDD]->getValeur();
        $emailBU = $variables[Variable::CODE_EMAIL_BU]->getValeur();

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

        if ($this->estDevalidation) {
            $this->setSubject("Annulation de la validation effectuée à l'issue du rendez-vous avec la BU");
        } else {
            $this->setSubject("Validation à l'issue du rendez-vous avec la BU");
        }

        $this->setTemplateVariables([
            'doctorant' => $doctorant,
            'estDevalidation' => $this->estDevalidation,
            'notifierDoctorantImpossibleMessage' => $this->notifierDoctorantImpossibleMessage,
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getResultMessage()
    {
        if ($this->estDevalidation) {
            return sprintf(
                "Un mail de notification vient d'être envoyé à la BU (%s) avec copie au Bureau des Doctorats (%s).",
                $this->getTo(),
                $this->getCc()
            );
        } else {
            if ($this->notifierDoctorant) {
                return sprintf(
                    "Un mail de notification vient d'être envoyé à %s avec copie au Bureau des Doctorats (%s)",
                    $this->these->getDoctorant(),
                    $this->getCc()
                );
            } else {
                return sprintf(
                    "Un mail de notification vient d'être envoyé au Bureau des Doctorats (%s).",
                    $this->getTo()
                );
            }
        }
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
}