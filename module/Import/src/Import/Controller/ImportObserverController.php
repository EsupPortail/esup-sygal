<?php

namespace Import\Controller;

use Application\Entity\Db\Source;
use Application\EventRouterReplacerAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Import\Model\ImportObserv;
use Import\Model\Service\ImportObservResultServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use Unicaen\Console\Controller\AbstractConsoleController;
use UnicaenApp\Exception\RuntimeException;
use UnicaenDbImport\Entity\Db\Service\ImportObserv\ImportObservServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 *
 *
 * @author Unicaen
 */
class ImportObserverController extends AbstractConsoleController
{
    use ImportObservServiceAwareTrait;
    use EventRouterReplacerAwareTrait;
    use ImportObservResultServiceAwareTrait;
    use TheseServiceAwareTrait;
    use SourceServiceAwareTrait;

    /**
     * Console action.
     *
     * Action adressable en ligne de commande qui traite les résultats d'observation
     * de certains changements durant la synchro.
     *
     * Cette action doit être lancée périodiquement (au moins une fois par jour).
     *
     * Ligne de commande :
     * php ./public/index.php process-observed-import-results --source=UCN::apogee [--import-observ=12345] [--source-code=UCN::1234]
     */
    public function processObservedImportResultsAction(): void
    {
        $codeSource = $this->params('source'); // ex : 'UCN::apogee'
        $codeImportObserv = $this->params('import-observ');
        $sourceCodeThese = $this->params('source-code');

        if ($codeImportObserv === null) {
            $codes = ImportObserv::CODES;
        } else {
            Assert::inArray($codeImportObserv, ImportObserv::CODES);
            $codes = (array) $codeImportObserv;
        }

        /** @var These $these */
        $these = null;
        if ($sourceCodeThese !== null) {
            $these = $this->theseService->getRepository()->findOneBy(['sourceCode' => $sourceCodeThese]);
            if ($these === null) {
                throw new RuntimeException("Aucune thèse trouvée avec le source code '$sourceCodeThese''");
            }
        }

        /** @var Source $source */
        $source = null;
        if ($codeSource !== null) {
            $source = $this->sourceService->getRepository()->findOneBy(['code' => $codeSource]);
            if ($source === null) {
                throw new RuntimeException("Aucune Source trouvée avec le code '$codeSource''");
            }
        }

        $criteria = array_filter(compact('source', 'these'));

        $this->eventRouterReplacer->replaceEventRouter($this->getEvent());

        foreach ($codes as $code) {
            /** @var \Import\Model\ImportObserv|null $importObserv */
            $importObserv = $this->importObservService->getRepository()->findOneBy(['code' => $code]);
            if ($importObserv === null) {
                throw new RuntimeException("Aucun enregistrement ImportObserv trouvé avec le code '$code'");
            }

            $this->importObservResultService->processImportObserv($importObserv, $criteria);
        }

        $this->eventRouterReplacer->restoreEventRouter();

        exit(0);
    }
}
