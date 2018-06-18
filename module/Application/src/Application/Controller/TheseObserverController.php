<?php

namespace Application\Controller;

use Application\EventRouterReplacerAwareTrait;
use Application\Service\These\TheseObserverServiceAwareTrait;

/**
 *
 *
 * @author Unicaen
 */
class TheseObserverController extends AbstractController
{
    use EventRouterReplacerAwareTrait;
    use TheseObserverServiceAwareTrait;

    /**
     * Console action.
     *
     * Action adressable en ligne de commande.
     *
     * Ligne de commande :
     * php ./public/index.php notify-date-butoir-correction-depassee
     *
     * Exemple de config CRON :
     * 0 8 * * 1-5 root /usr/bin/php /home/gauthierb/workspace/sygal/public/index.php notify-date-butoir-correction-depassee 1> /tmp/sodoctlog.txt 2>&1
     * i.e. du lundi au vendredi, à 8:00.
     */
    public function notifyDateButoirCorrectionDepasseeAction()
    {
        $this->eventRouterReplacer->replaceEventRouter($this->getEvent());

        $this->theseObserverService->handleThesesWithDateButoirCorrectionDepassee();

        $this->eventRouterReplacer->restoreEventRouter();

        exit(0);
    }
}
