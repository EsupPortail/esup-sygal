<?php

namespace StepStar\Controller\Generate;

use StepStar\Facade\Generate\GenerateFacadeAwareTrait;
use StepStar\Service\Fetch\FetchServiceAwareTrait;
use Unicaen\Console\Controller\AbstractConsoleController;

class GenerateConsoleController extends AbstractConsoleController
{
    use GenerateFacadeAwareTrait;
    use FetchServiceAwareTrait;

    /**
     * Action pour générer les fichiers nécessaires à l'envoi de plusieurs theses vers STEP/STAR.
     */
    public function genererThesesAction(): void
    {
        $command = implode(' ', $this->getRequest()->getContent());

        $these = $this->params()->fromRoute('these'); // ex : '12345' ou '12345,12346'
        $etat = $this->params()->fromRoute('etat'); // ex : 'E' ou 'E,S'
        $dateSoutenanceMin = $this->params()->fromRoute('date-soutenance-min'); // ex : '2022-03-11' ou '6m'
        $etablissement = $this->params()->fromRoute('etablissement'); // ex : 'UCN' ou 'UCN,URN'
        $tag = $this->params()->fromRoute('tag');

        $criteria = array_filter(compact('these', 'etat', 'dateSoutenanceMin', 'etablissement'));

        $theses = $this->fetchService->fetchThesesByCriteria($criteria);
        if (empty($theses)) {
            $this->console->write("Aucune these trouvee avec les criteres specifies.");
            exit(0);
        }

        $logs = $this->generateFacade->generateFilesForTheses($theses, $command, $tag);

        /** @var \StepStar\Entity\Db\Log $log */
        foreach ($logs as $log) {
            $this->console->write("These " . $log->getTheseId());
            $this->console->write($log->getLog());
        }
    }
}