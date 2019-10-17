<?php

namespace Application\Notification;

use Application\Entity\Db\Interfaces\TheseAwareTrait;
use Notification\Notification;
use UnicaenApp\Exception\LogicException;

class CorrectionAttendueUpdatedNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'application/these/mail/notif-depot-version-corrigee-attendu';

    /**
     * Initialisation, préparation, etc. nécessaires avant de pouvoir envoyer la notification.
     *
     * @return static
     */
    public function prepare()
    {
        if ($this->estPremiereNotif === null) {
            throw new LogicException("Attribut estPremiereNotif non sp avant l'appel de prepare()");
        }

        $directeursTheseEnCopie = false;
        if ($this->these->getCorrectionAutoriseeEstObligatoire() && !$this->estPremiereNotif) {
            $directeursTheseEnCopie = true;
        }

        $to = $this->these->getDoctorant()->getEmailPro() ?: $this->these->getDoctorant()->getEmail();

        $cc = null;
        if ($directeursTheseEnCopie) {
            $cc = $this->these->getDirecteursTheseEmails();
        }

        $subject = "Dépôt de thèse, corrections " . lcfirst($this->these->getCorrectionAutoriseeToString()) . "s attendues";

        $this->setSubject($subject);
        $this->setTo($to);
        $this->setCc($cc);

        $this->setTemplateVariables([
            'these' => $this->these,
            'estPremiereNotif' => $this->estPremiereNotif,
        ]);

        return $this;
    }

    /**
     * @var bool
     */
    private $estPremiereNotif;

    /**
     * @param bool $estPremiereNotif
     * @return $this
     */
    public function setEstPremiereNotif($estPremiereNotif = true)
    {
        $this->estPremiereNotif = $estPremiereNotif;

        return $this;
    }

}