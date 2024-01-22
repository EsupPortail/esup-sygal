<?php

namespace Depot\Notification;

use Notification\Exception\RuntimeException;
use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;
use UnicaenApp\Exception\LogicException;

class ChangementCorrectionAttendueNotification extends Notification
{
    use TheseAwareTrait;

    protected ?string $templatePath = 'depot/depot/mail/notif-depot-version-corrigee-attendu';

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

        $individu = $this->these->getDoctorant()->getIndividu();
        $to = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();

        if (!$to) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$this->these->getDoctorant()}");
        }

        $cc = null;
        if ($directeursTheseEnCopie) {
            $cc = array_merge($this->these->getDirecteursTheseEmails(), $this->these->getCoDirecteursTheseEmails());
        }

        $subject = "Dépôt de thèse, corrections " . lcfirst($this->these->getCorrectionAutoriseeToString(true)) . " attendues";

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