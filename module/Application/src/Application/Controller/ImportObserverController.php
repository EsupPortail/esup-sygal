<?php

namespace Application\Controller;

use Application\EventRouterReplacerAwareTrait;
use Application\Service\ImportObservResult\ImportObservResultServiceAwareTrait;

/**
 *
 *
 * @author Unicaen
 */
class ImportObserverController extends AbstractController
{
    use EventRouterReplacerAwareTrait;
    use ImportObservResultServiceAwareTrait;

    /**
     * Console action.
     *
     * Action adressable en ligne de commande qui traite les résultats d'observation
     * de certains changements durant la synchro.
     *
     * Cette action doit être lancée périodiquement (au moins une fois par jour).
     *
     * Ligne de commande :
     * php ./public/index.php process-observed-import-results
     *
     * Exemple de config CRON :
     * 0 5-17 * * 1-5 root /usr/bin/php /home/gauthierb/workspace/sodoct/public/index.php process-observed-import-results 1> /tmp/sodoctlog.txt 2>&1
     * i.e. du lundi au vendredi, à 05:00, 06:00 ... 17:00
     */
    public function processObservedImportResultsAction()
    {
        $this->eventRouterReplacer->replaceEventRouter($this->getEvent());

        $this->importObservResultService
            ->handleImportObservResultsForResultatAdmis()
            ->handleImportObservResultsForCorrectionMineure()
            ->handleImportObservResultsForCorrectionMajeure();

        $this->eventRouterReplacer->restoreEventRouter();

        exit(0);
    }
}
