<?php

namespace Application\Notification;

use Application\Entity\Db\Interfaces\TheseAwareTrait;
use Notification\Notification;

class ResultatTheseAdmisNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'application/these/mail/notif-resultat-these-modifie';

    /**
     * @param array $context
     * @return static
     */
    public function prepare(array $context = [])
    {
        $to = $this->these->getDoctorant()->getEmailPro() ?: $this->these->getDoctorant()->getEmail();
        $this->setTo($to);
        $this->setSubject("Votre dossier est complet");

        $this->setTemplateVariables([
            'these' => $this->these,
            'contact' => $this->emailBdd,
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