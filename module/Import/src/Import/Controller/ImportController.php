<?php

namespace Import\Controller;

use Import\Service\FetcherService;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ImportController extends AbstractActionController
{
    use EntityManagerAwareTrait;

    /**
     * Liste ORDONNÉE de tous les services proposés.
     */
    const SERVICES = [
        'individu',
        'doctorant',
        'these',
        'role',
        'acteur',
        'variable',
        'structure',
        'etablissement',
        'ecole-doctorale',
        'unite-recherche',
    ];

    private $debug = false;

    /** @var $fetcherService FetcherService*/
    protected $fetcherService;

    public function __construct($fetcherService)
    {
        $this->fetcherService    = $fetcherService;
    }

    public function indexAction()
    {
        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery("SELECT REQ_END_DATE FROM API_LOG WHERE REQ_ETABLISSEMENT='UCN' AND REQ_TABLE='variable' ORDER BY REQ_END_DATE DESC");
        $last = $result->fetch()["REQ_END_DATE"];

        return new ViewModel([
            'last' => $last,
        ]);
    }

    public function infoLastUpdateAction() {
        $etablissement = $this->params()->fromRoute("etablissement");
        $table = $this->params()->fromRoute("table");

        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery("SELECT REQ_END_DATE, REQ_RESPONSE FROM API_LOG WHERE REQ_ETABLISSEMENT='".$etablissement."' AND REQ_TABLE='".$table."' ORDER BY REQ_END_DATE DESC");
        $data = $result->fetch();

        $last_time = $data["REQ_END_DATE"];
        $last_number = explode(" ",$data["REQ_RESPONSE"])[0];

        return  new ViewModel([
            'query' => $etablissement . '|' . $table,
            "last_time" => $last_time,
            "last_number" => $last_number,
        ]);
    }

    public function helpAction()
    {
        return new ViewModel();
    }

    /** fetchAction permet de récupérer les données d'un SI en fonction de paramètres spécifique donnés par la route
     *  'service' --- le nom du web service qui sera appelé (p.e. these, doctorant, ...)
     *  'etablissement' --- le sigle associé à l'établissement que l'on souhaite interroger (p.e. UCN, UCR, ...)
     *  'source_code' --- le source_code de l'entité à récupérer (p.e. '12047')
     *
     *  RMQ: 'service' et 'etablissement' sont pour le moment obligatoire.
     *  RMQ: si 'source_code' est non renseigné alors il faut récupérer toutes les données
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function fetchAction() {

        $service_para  = $this->params('service');
        $etablissement = $this->params('etablissement');
        $source_code   = $this->params('source_code');
        if ($this->debug) {
            echo "SERVICE: {$service_para}<br/>";
            echo "ETABLISSEMENT: {$etablissement}<br/>";
            echo "SOURCE_CODE: {$source_code}<br/>";
        }

        /** is it all ? */
        if ($service_para === "all") {
            $services = static::SERVICES;
        } else {
            $services = [ $service_para ];
        }

        $logs = [];
        foreach ($services as $service) {

            /** Paramétrage du service de récupération */
            $key = $this->fetcherService->getEtablissementKey($etablissement);
            $this->fetcherService->setConfigWithPosition($key);
            if ($this->debug) {
                echo "KEY: {$key}<br/>";
                echo $this->fetcherService->getCode() . " | ";
                echo $this->fetcherService->getUrl() . " | ";
                echo $this->fetcherService->getProxy() . " | ";
                echo (($this->fetcherService->getVerify())?"true":"false") . " <br/> ";

                echo $this->fetcherService->getUser() . " | ";
                echo $this->fetcherService->getPassword(). " | ";

            }

            $dataName = $service;
            $entityName = str_replace("-"," ", $service);
            $entityName = ucwords($entityName);
            $entityName = str_replace(" ","", $entityName);
            $entityClass = "Import\Model\Tmp" . $entityName;
            $source_code = ($source_code != "non renseigné") ? $source_code : null;

            /** Execution de la récupération */
//            var_dump($dataName);
//            var_dump($entityClass);
//            var_dump($source_code);
            try {
                $logs[] = $this->fetcherService->fetch($dataName, $entityClass, $source_code);
            } catch (\Exception $e) {
                print "<h1>Une exception a été levée (".$e->getCode()." - ".$e->getMessage() .")</h1>";
                throw $e;
            }
        }

        $this->fetcherService->updateBDD($services);

        return new ViewModel([
            'service' => $service_para,
            'etablissement' => $etablissement,
            'source_code' => $source_code,

            'logs' => $logs,
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function fetchConsoleAction() {

        $service_para  = $this->params('service');
        $etablissement = $this->params('etablissement');
        $source_code   = $this->params('source_code');

        /** is it all ? */
        if ($service_para === "all") {
            $services = static::SERVICES;
        } else {
            $services = [ $service_para ];
        }

        $logs = [];
        foreach ($services as $service) {

            /** Paramétrage du service de récupération */
            $key = $this->fetcherService->getEtablissementKey($etablissement);
            $this->fetcherService->setConfigWithPosition($key);

            $dataName = $service;
            $entityClass = "Import\Model\Tmp" . ucwords($service);
            $source_code = ($source_code != "non renseigné") ? $source_code : null;

            /** Execution de la récupération */
            try {
                $logs[] = $this->fetcherService->fetch($dataName, $entityClass, $source_code);
            } catch (\Exception $e) {
                print "Une exception a été levée (".$e->getCode()." - ".$e->getMessage() .")";
                throw $e;
            }
        }

        $this->fetcherService->updateBDD($services);

        print "Importation des données réussie \n";
    }
}
