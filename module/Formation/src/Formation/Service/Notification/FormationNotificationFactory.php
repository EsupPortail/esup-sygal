<?php

namespace Formation\Service\Notification;

use Formation\Entity\Db\Inscription;
use Formation\Provider\Template\MailTemplates;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\Notification;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class FormationNotificationFactory extends NotificationFactory
{
    use RenduServiceAwareTrait;

    /** INSCRIPTION ***************************************************************************************************/

    public function createNotificationInscriptionEnregistree(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_ENREGISTREE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationInscriptionListePrincipale(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_LISTE_PRINCIPALE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationInscriptionListeComplementaire(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
            'inscription' => $inscription,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_LISTE_COMPLEMENTAIRE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationInscriptionClose(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session' => $inscription->getSession(),
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_CLOSE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notif;
    }

    public function createNotificationInscriptionEchec(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_ECHEC, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    /** SESSIONS ******************************************************************************************************/

    public function createNotificationSessionImminente(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_IMMINENTE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationSessionTerminee(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_TERMINEE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationSessionAnnulee(Inscription $inscription): Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session' => $inscription->getSession(),
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_ANNULEE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notif;
    }
}