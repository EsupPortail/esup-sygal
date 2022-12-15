<?php

namespace These\Notification;

use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class ResultatTheseAdmisNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'these/these/mail/notif-resultat-admis-doctorant';

    /**
     * @var bool
     */
    protected $emailDoctorantAbsent;

    /**
     * @return string
     */
    public function __toString()
    {
        $toString = parent::__toString();

        if ($this->emailDoctorantAbsent) {
            $toString .= " NB: aucun email trouvé pour le doctorant.";
        }

        return $toString;
    }

    /**
     * @return static
     */
    public function prepare()
    {
        $individu = $this->these->getDoctorant()->getIndividu();
        $to = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();

        $this->emailDoctorantAbsent = false;
        if (! $to) {
            // lorsque le doctorant n'a pas d'email, envoi au BDD (+ petit message d'alerte dans le mail)
            $this->emailDoctorantAbsent = true;
            $to = $this->emailBdd;
        }

        $this->setTo($to);
        $this->setSubject("Votre dossier est complet");

        $this->setTemplateVariables([
            'these' => $this->these,
            'contact' => $this->emailBdd,
            'doctorant' => $this->these->getDoctorant(),
            'emailDoctorantAbsent' => $this->emailDoctorantAbsent,
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

    /**
     * @return bool
     */
    public function isEmailDoctorantAbsent()
    {
        return $this->emailDoctorantAbsent;
    }
}