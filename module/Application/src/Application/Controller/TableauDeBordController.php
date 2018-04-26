<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\SourceInterface;
use Application\Service\AnomalieServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Zend\View\Model\ViewModel;

class TableauDeBordController extends AbstractController
{
    use AnomalieServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    public function indexAction() {
        return new ViewModel();
    }

    public function anomalieAction() {


        $etablissement = $this->params()->fromQuery("etablissement");
        $anomalies = $this->getAnomalieService()->getAnomalies($etablissement);

        foreach ($anomalies as $anomalie) {
            $anomaliesTables[$anomalie->getTableName()][] = $anomalie;
        }

        $etablissements = $this->getEtablissementService()->getEtablissementsBySource(SourceInterface::CODE_SYGAL);
        $etablissements = array_filter($etablissements, function (Etablissement $etablissement) { return count($etablissement->getStructure()->getStructuresSubstituees())==0; });
        $etablissements = array_filter($etablissements, function (Etablissement $etablissement) { return $etablissement->getSigle() != "NU";});

        return new ViewModel([
            'anomaliesTables' => $anomaliesTables,
            'etablissements' => $etablissements,
        ]);
    }
}