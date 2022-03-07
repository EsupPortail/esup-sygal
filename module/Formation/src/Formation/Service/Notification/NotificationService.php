<?php

namespace Formation\Service\Notification;

use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Notification\Notification;
use Notification\Service\NotifierService;
use Laminas\View\Helper\Url as UrlHelper;

class NotificationService extends NotifierService
{
    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    public function setUrlHelper($urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /** Mail lié à l'inscription à une formation **********************************************************************/

    public function triggerInscriptionEnregistree(Inscription $inscription)
    {
        $mail = $inscription->getDoctorant()->getIndividu()->getEmail();
        $libelle = $inscription->getSession()->getFormation()->getLibelle();

        if ($mail !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Validation de votre inscription à la session de formation ".$libelle)
                ->setTo($mail)
                ->setTemplatePath('formation/notification/inscription-enregistree')
                ->setTemplateVariables([
                    'inscription' => $inscription,
                    'libelle' => $libelle,
                ]);
            $this->trigger($notif);
        }
    }

    public function triggerInscriptionListePrincipale(Inscription $inscription)
    {
        $mail = $inscription->getDoctorant()->getIndividu()->getEmail();
        $libelle = $inscription->getSession()->getFormation()->getLibelle();

        if ($mail !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Vous êtes sur la liste principale de la formation ".$libelle)
                ->setTo($mail)
                ->setTemplatePath('formation/notification/inscription-principale')
                ->setTemplateVariables([
                    'inscription' => $inscription,
                    'libelle' => $libelle,
                ]);
            $this->trigger($notif);
        }
    }

    public function triggerInscriptionListeComplementaire(Inscription $inscription)
    {
        $mail = $inscription->getDoctorant()->getIndividu()->getEmail();
        $libelle = $inscription->getSession()->getFormation()->getLibelle();

        if ($mail !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Vous êtes sur la liste complementaire de la formation ".$libelle)
                ->setTo($mail)
                ->setTemplatePath('formation/notification/inscription-complementaire')
                ->setTemplateVariables([
                    'inscription' => $inscription,
                    'libelle' => $libelle,
                ]);
            $this->trigger($notif);
        }
    }

    /** Mail de rappel ************************************************************************************************/

    /**
     * @param Session $session
     * @see Application/view/formation/notification/session-imminente.phtml
     */
    public function triggerInscriptionEchec(Session $session)
    {
        /** @var Inscription[] $inscriptions */
        $inscriptions = $session->getListeComplementaire();
        $libelle = $session->getFormation()->getLibelle(). " #".$session->getIndex();

        foreach ($inscriptions as $inscription) {
            $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

            if ($mail !== null) {
                $notif = new Notification();
                $notif
                    ->setSubject("Inscription à la formation ".$libelle." impossible.")
                    ->setTo($mail)
                    ->setTemplatePath('formation/notification/inscription-echec')
                    ->setTemplateVariables([
                        'inscription' => $inscription,
                        'libelle' => $libelle,
                    ]);
                $this->trigger($notif);
            }
        }
    }

    /**
     * @param Session $session
     * @see Application/view/formation/notification/session-imminente.phtml
     */
    public function triggerSessionImminente(Session $session)
    {
        /** @var Inscription[] $inscriptions */
        $inscriptions = $session->getListePrincipale();
        $libelle = $session->getFormation()->getLibelle(). " #".$session->getIndex();

        foreach ($inscriptions as $inscription) {
            $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

            if ($mail !== null) {
                $notif = new Notification();
                $notif
                    ->setSubject("La session de formation ".$libelle." va bientôt commencée.")
                    ->setTo($mail)
                    ->setTemplatePath('formation/notification/session-imminente')
                    ->setTemplateVariables([
                        'inscription' => $inscription,
                        'libelle' => $libelle,
                    ]);
                $this->trigger($notif);
            }
        }
    }

    /**
     * @param Session $session
     * @see Application/view/formation/notification/session-imminente.phtml
     */
    public function triggerSessionTerminee(Session $session)
    {
        /** @var Inscription[] $inscriptions */
        $inscriptions = $session->getListePrincipale();
        $libelle = $session->getFormation()->getLibelle(). " #".$session->getIndex();

        foreach ($inscriptions as $inscription) {
            $mail = $inscription->getDoctorant()->getIndividu()->getEmail();

            if ($mail !== null) {
                $notif = new Notification();
                $notif
                    ->setSubject("La session de formation ".$libelle." est maintenant terminée.")
                    ->setTo($mail)
                    ->setTemplatePath('formation/notification/session-terminee')
                    ->setTemplateVariables([
                        'inscription' => $inscription,
                        'libelle' => $libelle,
                    ]);
                $this->trigger($notif);
            }
        }
    }

}