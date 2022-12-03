<?php

namespace StepStar\Controller\Envoi;

use InvalidArgumentException;
use Laminas\Mvc\Console\Controller\AbstractConsoleController;
use StepStar\Facade\EnvoiFacadeAwareTrait;
use StepStar\Service\Fetch\FetchServiceAwareTrait;

class EnvoiConsoleController extends AbstractConsoleController
{
    use EnvoiFacadeAwareTrait;

    use FetchServiceAwareTrait;

    /**
     * Action pour envoyer plusieurs theses vers STEP/STAR.
     */
    public function envoyerThesesAction()
    {
        $command = implode(' ', $this->getRequest()->getContent());

        $these = $this->params()->fromRoute('these'); // ex : '12345' ou '12345,12346'
        $etat = $this->params()->fromRoute('etat'); // ex : 'E' ou 'E,S'
        $dateSoutenanceMin = $this->params()->fromRoute('date-soutenance-min'); // ex : '2022-03-11' ou '6m'
        $etablissement = $this->params()->fromRoute('etablissement'); // ex : 'UCN' ou 'UCN,URN'
        $force = (bool) $this->params()->fromRoute('force');
        $logTag = $this->params()->fromRoute('tag');

        $criteria = array_filter(compact('these', 'etat', 'dateSoutenanceMin', 'etablissement'));

        $theses = $this->fetchService->fetchThesesByCriteria($criteria);
        if (empty($theses)) {
            throw new InvalidArgumentException("Aucune these trouvee avec les criteres specifies");
        }

        $this->envoiFacade->setSaveLogs(true);
        $logs = $this->envoiFacade->envoyerTheses($theses, $force, $command, $logTag);

        /** @var \StepStar\Entity\Db\Log $log */
        foreach ($logs as $log) {
            $this->console->write($log->getLog());
        }
    }
}