<?php

namespace Application\Notification;

use Application\Entity\Db\Interfaces\TheseAwareTrait;
use Notification\Notification;

class ResultatTheseAdmisNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'application/import/mail/notif-resultat-admis-doctorant';

    /**
     * @return static
     */
    public function prepare()
    {
        $to = $this->these->getDoctorant()->getEmailPro() ?: $this->these->getDoctorant()->getEmail();

        $emailDoctorantAbsent = false;
        if (! $to) {
            // lorsque le doctorant n'a pas d'email, envoi au BDD (+ petit message d'alerte dans le mail)
            $emailDoctorantAbsent = true;
            $to = $this->emailBdd;
        }

        $this->setTo($to);
        $this->setSubject("Votre dossier est complet");

        $this->setTemplateVariables([
            'these' => $this->these,
            'contact' => $this->emailBdd,
            'doctorant' => $this->these->getDoctorant(),
            'emailDoctorantAbsent' => $emailDoctorantAbsent,
        ]);

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
}