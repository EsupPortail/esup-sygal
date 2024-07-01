<?php

namespace Depot\Notification;

use Notification\Exception\RuntimeException;
use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class ChangementCorrectionAttendueNotification extends Notification
{
    use TheseAwareTrait;

    protected ?string $templatePath = 'depot/depot/mail/notif-depot-version-corrigee-attendu';

    private bool $estPremiereNotif;

    public function setEstPremiereNotif(bool $estPremiereNotif = true): static
    {
        $this->estPremiereNotif = $estPremiereNotif;

        return $this;
    }

    /**
     * Initialisation, préparation, etc. nécessaires avant de pouvoir envoyer la notification.
     */
    public function prepare(): static
    {
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
}