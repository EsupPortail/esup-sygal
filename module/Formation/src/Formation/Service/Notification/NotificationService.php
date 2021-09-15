<?php

namespace Formation\Service\Notification;

use Application\Entity\Db\Validation;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Notification\Notification;
use Notification\Service\NotifierService;
use Zend\View\Helper\Url as UrlHelper;

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
}