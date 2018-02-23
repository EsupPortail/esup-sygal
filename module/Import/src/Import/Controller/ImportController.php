<?php

namespace Import\Controller;

use Import\Service\FetcherService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ImportController extends AbstractActionController
{

    /** @var $fetcherService FetcherService*/
    protected $fetcherService;

    public function __construct($fetcherService)
    {
        $this->fetcherService    = $fetcherService;
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

        /** is it all ? */
        if ($service_para === "all") {
            $services = ['source', 'variable', 'role', 'doctorant', 'these', 'individu', 'acteur'];
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
                print "<h1>Une exception a été levée (".$e->getCode()." - ".$e->getMessage() .")</h1>";
                throw $e;
            }
        }

        $this->fetcherService->updateBDD();
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
            $services = ['source', 'variable', 'role', 'doctorant', 'these', 'individu', 'acteur'];
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

        $this->fetcherService->updateBDD();
        print "Importation des données réussie \n";

    }
}
