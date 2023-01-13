<?php

namespace Soutenance\Service\Notification;

use Notification\Notification;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Template\MailTemplates;
use Soutenance\Service\Url\UrlServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class NotificationService extends \Notification\Service\NotifierService
{
    use RenduServiceAwareTrait;
    use UrlServiceAwareTrait;

    public function triggerTransmettreDocumentsDirectionThese(These $these, Proposition $proposition) : void
    {
        $vars = ['these' => $these, 'proposition' => $proposition, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::TRANSMETTRE_DOCUMENTS_DIRECTION, $vars);
        $mail = $these->getDirecteursTheseEmails();

        if (!empty($mail !== null)) {
            $notif = new Notification();
            $notif
                ->setSubject($rendu->getSujet())
                ->setTo($mail)
                ->setBody($rendu->getCorps());
            $this->trigger($notif);
        }

        exit();
    }
}