<?php

namespace StepStar\Controller\Envoi;

use StepStar\Facade\Envoi\EnvoiFacadeAwareTrait;
use StepStar\Service\Fetch\FetchServiceAwareTrait;
use Unicaen\Console\Controller\AbstractConsoleController;

class EnvoiConsoleController extends AbstractConsoleController
{
    use EnvoiFacadeAwareTrait;

    use FetchServiceAwareTrait;

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

        $these = $this->params()->fromRoute('these'); // ex : '12345' ou '12345,12346'
        $etat = $this->params()->fromRoute('etat'); // ex : 'E' ou 'E,S'
        $dateSoutenanceNull = (bool) $this->params()->fromRoute('date-soutenance-null');
        $dateSoutenanceMin = $this->params()->fromRoute('date-soutenance-min'); // ex : '2022-03-11' ou 'P6M'
        $dateSoutenanceMax = $this->params()->fromRoute('date-soutenance-max'); // ex : '2022-03-11' ou 'P6M'
        $etablissement = $this->params()->fromRoute('etablissement'); // ex : 'UCN' ou 'UCN,URN'
        $force = (bool) $this->params()->fromRoute('force');
        $tag = $this->params()->fromRoute('tag');

        $criteria = array_filter(compact('these', 'etat', 'dateSoutenanceNull', 'dateSoutenanceMin', 'dateSoutenanceMax', 'etablissement'));

        $theses = $this->fetchService->fetchThesesByCriteria($criteria);
        if (empty($theses)) {
            $this->console->write("Aucune these trouvee avec les criteres specifies.");
            exit(0);
        }

        $this->envoiFacade->setSaveLogs(true);
        $logs = $this->envoiFacade->envoyerTheses($theses, $force, $command, $tag);

        /** @var \StepStar\Entity\Db\Log $log */
        foreach ($logs as $log) {
            $this->console->write($log->getLog());
        }
    }
}