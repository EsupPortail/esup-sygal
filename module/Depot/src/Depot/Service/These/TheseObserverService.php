<?php

namespace Depot\Service\These;

use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use These\Service\Notification\TheseNotificationFactoryAwareTrait;
use These\Service\These\TheseServiceAwareTrait;

class TheseObserverService
{
    use TheseServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use TheseNotificationFactoryAwareTrait;

    /**
     * Notification systématique à propos des thèses dont la date butoir pour le dépôt de la version corrigée est dépassée.
     */
    public function handleThesesWithDateButoirCorrectionDepassee()
    {
        $theses = $this->theseService->getRepository()->fetchThesesWithDateButoirDepotVersionCorrigeeDepassee();

        foreach ($theses as $these) {
            try {
                $notif = $this->theseNotificationFactory->createNotificationDateButoirCorrectionDepassee($these);
                $this->notifierService->trigger($notif);
            } catch (RuntimeException $e) {
                // aucun destinataire, todo : gérer le cas !
            }
        }
    }
}