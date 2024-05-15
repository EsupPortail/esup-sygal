<?php

namespace StepStar\Controller\Envoi;

use StepStar\Controller\CriteriaAwareControllerTrait;
use StepStar\Facade\Envoi\EnvoiFacadeAwareTrait;
use StepStar\Service\Fetch\FetchServiceAwareTrait;
use Unicaen\Console\Controller\AbstractConsoleController;

class EnvoiConsoleController extends AbstractConsoleController
{
    use EnvoiFacadeAwareTrait;
    use FetchServiceAwareTrait;
    use CriteriaAwareControllerTrait;

    /**
     * Action pour envoyer des fichiers TEF vers STEP/STAR.
     */
    public function envoyerFichiersAction(): void
    {
        $dir = $this->params()->fromRoute('dir');

        $this->envoiFacade->setSaveLogs(true);
        $logs = $this->envoiFacade->envoyerFichiers($dir);

        /** @var \StepStar\Entity\Db\Log $log */
        foreach ($logs as $log) {
            $this->console->write($log->getLog());
        }
    }

    /**
     * Action pour envoyer des theses vers STEP/STAR.
     */
    public function envoyerThesesAction(): void
    {
        $command = implode(' ', $this->getRequest()->getContent());

        $this->loadCriteriaFromControllerParams($this);
        $criteria = $this->getCriteriaAsArray();

        $theses = $this->fetchService->fetchThesesByCriteria($criteria);
        $criteriaToStrings = $this->fetchService->getCriteriaToStrings();

        $this->console->writeLine("Criteres de recherche specifies :");
        foreach ($criteriaToStrings as $str) {
            $this->console->writeLine("  - " . $str);
        }
        if (empty($theses)) {
            $this->console->writeLine("Aucune these trouvee avec les criteres specifies.");
            exit(0);
        }
        $this->console->writeLine("Nombre de theses trouvees : " . count($theses));

        $this->envoiFacade->setSaveLogs(true);
        $logs = $this->envoiFacade->envoyerTheses($theses, $this->force, $command, $this->tag);

        /** @var \StepStar\Entity\Db\Log $log */
        foreach ($logs as $log) {
            $this->console->writeLine($log->getLog());
        }
    }
}