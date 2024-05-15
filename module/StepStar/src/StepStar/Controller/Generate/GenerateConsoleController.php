<?php

namespace StepStar\Controller\Generate;

use StepStar\Controller\CriteriaAwareControllerTrait;
use StepStar\Facade\Generate\GenerateFacadeAwareTrait;
use StepStar\Service\Fetch\FetchServiceAwareTrait;
use Unicaen\Console\Controller\AbstractConsoleController;

class GenerateConsoleController extends AbstractConsoleController
{
    use GenerateFacadeAwareTrait;
    use FetchServiceAwareTrait;
    use CriteriaAwareControllerTrait;

    /**
     * Action pour générer les fichiers nécessaires à l'envoi de plusieurs theses vers STEP/STAR.
     */
    public function genererThesesAction(): void
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

        $logs = $this->generateFacade->generateFilesForTheses($theses, $command, $this->tag);

        /** @var \StepStar\Entity\Db\Log $log */
        foreach ($logs as $log) {
            $this->console->writeLine($log->getLog());
        }
    }
}