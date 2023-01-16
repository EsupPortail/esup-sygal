<?php

namespace These\Notification;

use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class ResultatTheseAdmisNotification extends Notification
{
    use TheseAwareTrait;

    protected ?string $templatePath = 'these/these/mail/notif-resultat-admis-doctorant';

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
            $to = $this->emailsBdd;
        }

        $this->setTo($to);
        $this->setSubject("Votre dossier est complet");

        $this->setTemplateVariables([
            'these' => $this->these,
            'doctorant' => $this->these->getDoctorant(),
            'emailDoctorantAbsent' => $this->emailDoctorantAbsent,
        ]);

        return $this;
    }

    private array $emailsBdd = [];

    public function setEmailsBdd(array $emailBdd): self
    {
        $this->emailsBdd = $emailBdd;

        return $this;
    }

    public function isEmailDoctorantAbsent(): bool
    {
        return $this->emailDoctorantAbsent;
    }
}