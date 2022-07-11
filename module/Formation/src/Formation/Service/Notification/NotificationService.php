<?php

namespace Formation\Service\Notification;

use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\Template\MailTemplates;
use Notification\Exception\NotificationException;
use Notification\Notification;
use Notification\Service\NotifierService;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class NotificationService extends NotifierService
{
    use RenduServiceAwareTrait;

    /** INSCRIPTION ***************************************************************************************************/

    /**
     * @param Inscription $inscription
     * @return void
     * @throws NotificationException
     */
    public function triggerInscriptionEnregistree(Inscription $inscription) : void
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_ENREGISTREE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

        if ($mail !== null) {
            $notif = new Notification();
            $notif
                ->setTo($mail)
                ->setSubject($rendu->getSujet())
                ->setBody($rendu->getCorps())
            ;
            $this->trigger($notif);
        }
    }

    /**
     * @param Inscription $inscription
     * @return void
     * @throws NotificationException
     */
    public function triggerInscriptionListePrincipale(Inscription $inscription) : void
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_LISTE_PRINCIPALE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

        if ($mail !== null) {
            $notif = new Notification();
            $notif
                ->setTo($mail)
                ->setSubject($rendu->getSujet())
                ->setBody($rendu->getCorps())
                ;
            $this->trigger($notif);
        }
    }

    /**
     * @param Inscription $inscription
     * @return void
     * @throws NotificationException
     */
    public function triggerInscriptionListeComplementaire(Inscription $inscription) : void
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_LISTE_COMPLEMENTAIRE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

        if ($mail !== null) {
            $notif = new Notification();
            $notif
                ->setTo($mail)
                ->setSubject($rendu->getSujet())
                ->setBody($rendu->getCorps())
            ;
            $this->trigger($notif);
        }
    }

    /**
     * @param Session $session
     * @return void
     * @throws NotificationException
     */
    public function triggerInscriptionEchec(Session $session) : void
    {
        $inscriptions = $session->getListeComplementaire();

        foreach ($inscriptions as $inscription) {
            $vars = [
                'doctorant' => $inscription->getDoctorant(),
                'formation' => $inscription->getSession()->getFormation(),
                'session'   => $inscription->getSession(),
            ];
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_ECHEC, $vars);
            $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

            if ($mail !== null) {
                $notif = new Notification();
                $notif
                    ->setTo($mail)
                    ->setSubject($rendu->getSujet())
                    ->setBody($rendu->getCorps())
                ;
                $this->trigger($notif);
            }
        }
    }

    /** SESSIONS ******************************************************************************************************/

    /**
     * @param Session $session
     * @return void
     * @throws NotificationException
     */
    public function triggerSessionImminente(Session $session) : void
    {
        $inscriptions = $session->getListePrincipale();

        foreach ($inscriptions as $inscription) {
            $vars = [
                'doctorant' => $inscription->getDoctorant(),
                'formation' => $inscription->getSession()->getFormation(),
                'session'   => $inscription->getSession(),
            ];
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_IMMINENTE, $vars);
            $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

            if ($mail !== null) {
                $notif = new Notification();
                $notif
                    ->setTo($mail)
                    ->setSubject($rendu->getSujet())
                    ->setBody($rendu->getCorps())
                ;
                $this->trigger($notif);
            }
        }
    }

    /**
     * @param Session $session
     * @return void
     * @throws NotificationException
     */
    public function triggerSessionTerminee(Session $session) : void
    {
        $inscriptions = $session->getListePrincipale();

        foreach ($inscriptions as $inscription) {
            $vars = [
                'doctorant' => $inscription->getDoctorant(),
                'formation' => $inscription->getSession()->getFormation(),
                'session'   => $inscription->getSession(),
            ];
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_TERMINEE, $vars);
            $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

            if ($mail !== null) {
                $notif = new Notification();
                $notif
                    ->setTo($mail)
                    ->setSubject($rendu->getSujet())
                    ->setBody($rendu->getCorps())
                ;
                $this->trigger($notif);
            }
        }
    }

}