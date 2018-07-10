<?php

namespace Import\Controller;

use Import\Service\Traits\ImportServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ImportController extends AbstractActionController
{
    use EntityManagerAwareTrait;
    use ImportServiceAwareTrait;

    public function indexAction()
    {
        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery("SELECT REQ_END_DATE FROM API_LOG WHERE REQ_ETABLISSEMENT='UCN' AND REQ_TABLE='variable' ORDER BY REQ_END_DATE DESC");
        $last = $result->fetch()["REQ_END_DATE"];

        return new ViewModel([
            'last' => $last,
        ]);
    }

    public function infoLastUpdateAction()
    {
        $etablissement = $this->params()->fromRoute("etablissement");
        $table = $this->params()->fromRoute("table");

        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery("SELECT REQ_END_DATE, REQ_RESPONSE FROM API_LOG WHERE REQ_ETABLISSEMENT='" . $etablissement . "' AND REQ_TABLE='" . $table . "' ORDER BY REQ_END_DATE DESC");
        $data = $result->fetch();

        $last_time = $data["REQ_END_DATE"];
        $last_number = explode(" ", $data["REQ_RESPONSE"])[0];

        return new ViewModel([
            'query'       => $etablissement . '|' . $table,
            "last_time"   => $last_time,
            "last_number" => $last_number,
        ]);
    }

    public function helpAction()
    {
        return new ViewModel();
    }

    /**
     * Interroge le WS pour récupérer les données d'un seul établissement puis lance la synchronisation des données obtenues
     * avec les tables destinations.
     *
     * @return ViewModel
     */
    public function importAction()
    {
        $service = $this->params('service');
        $etablissement = $this->params('etablissement');
        $sourceCode = $this->params('source_code');

        $queryParams = $this->params()->fromQuery();

        $logs = [];
        $logs[] = $this->importService->import($service, $etablissement, $sourceCode, $queryParams);

        return new ViewModel([
            'service'       => $service,
            'etablissement' => $etablissement,
            'source_code'   => $sourceCode,
            'logs'          => $logs,
        ]);
    }

    /**
     * Interroge le WS pour récupérer toutes les données d'un établissement puis lance la synchronisation
     * des données obtenues avec les tables destinations.
     *
     * @return ViewModel
     */
    public function importAllAction()
    {
        $etablissement = $this->params('etablissement');
        $logs = [];

        $logs[] = $this->importService->importAll($etablissement);

        return new ViewModel([
            'service'       => 'Tous',
            'etablissement' => $etablissement,
            'source_code'   => '-',
            'logs'          => $logs,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function updateTheseAction()
    {
        $etablissement = $this->params('etablissement');
        $sourceCodeThese = $this->params('source_code');

        if (! $sourceCodeThese) {
            throw new LogicException("Le source code de la thèse est requis");
        }

        $logs = $this->importService->updateThese($etablissement, $sourceCodeThese);

        return new ViewModel([
            'service'       => "these + dépendances",
            'etablissement' => $etablissement,
            'source_code'   => $sourceCodeThese,
            'logs'          => $logs,
        ]);
    }

    public function fetchConsoleAction()
    {
        $service = $this->params('service');
        $etablissement = $this->params('etablissement');
        $sourceCode = $this->params('source_code');

        $this->importService->import($service, $etablissement, $sourceCode);

        echo "Importation des données du service '$service' de l'établissement '$etablissement' réussie." . PHP_EOL;
    }

    public function fetchAllConsoleAction()
    {
        $etablissement = $this->params('etablissement');

        $this->importService->importAll($etablissement);

        echo "Importation de toutes les données de l'établissement '$etablissement' réussie" . PHP_EOL;
    }
}
