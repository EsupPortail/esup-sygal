<?php

namespace Import\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\ImportObserv;
use Application\EventRouterReplacerAwareTrait;
use Assert\Assertion;
use Import\Service\ImportObserv\ImportObservServiceAwareTrait;
use Import\Service\ImportObservResult\ImportObservResultServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

/**
 *
 *
 * @author Unicaen
 */
class ImportObserverController extends AbstractController
{
    use ImportObservServiceAwareTrait;
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
     * php ./public/index.php process-observed-import-results --etablissement=UCN
     *
     * Exemple de config CRON :
     * 0 5-17 * * 1-5 root /usr/bin/php /home/gauthierb/workspace/sygal/public/index.php process-observed-import-results 1> /tmp/sodoctlog.txt 2>&1
     * i.e. du lundi au vendredi, à 05:00, 06:00 ... 17:00
     */
    public function processObservedImportResultsAction()
    {
        $etablissement = $this->params('etablissement');
        $codeImportObserv = $this->params('import-observ');

        if ($codeImportObserv === null) {
            $codes = ImportObserv::CODES;
        } else {
            Assertion::inArray($codeImportObserv, ImportObserv::CODES);
            $codes = (array) $codeImportObserv;
        }

        $this->eventRouterReplacer->replaceEventRouter($this->getEvent());

        foreach ($codes as $code) {
            /** @var ImportObserv|null $importObserv */
            $importObserv = $this->importObservService->getRepository()->findOneBy(['code' => $code]);
            if ($importObserv === null) {
                throw new RuntimeException("Aucune enregistrement ImportObserv trouvé avec le code '$importObserv'");
            }

            $this->importObservResultService->handleImportObservResults($importObserv, $etablissement);
        }

        $this->eventRouterReplacer->restoreEventRouter();

        exit(0);
    }
}
