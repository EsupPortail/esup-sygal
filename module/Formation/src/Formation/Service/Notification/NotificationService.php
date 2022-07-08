<?php

namespace Formation\Service\Notification;

use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\Template\MailTemplates;
use Notification\Notification;
use Notification\Service\NotifierService;
use Laminas\View\Helper\Url as UrlHelper;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class NotificationService extends NotifierService
{
    use RenduServiceAwareTrait;
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
//                ->setTemplatePath('formation/notification/renderer-mail')
//                ->setTemplateVariables([
//                    'corps' => $rendu->getCorps()])
                ;
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