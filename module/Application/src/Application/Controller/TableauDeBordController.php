<?php

namespace Application\Controller;

use Structure\Entity\Db\Etablissement;
use Application\Service\AnomalieServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Laminas\View\Model\ViewModel;

class TableauDeBordController extends AbstractController
{
    use SourceServiceAwareTrait;
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

        $source = $this->sourceService->fetchApplicationSource();
        $etablissements = $this->getEtablissementService()->getRepository()->findAllBySource($source->getCode());
        $etablissements = array_filter($etablissements, function (Etablissement $etablissement) { return count($etablissement->getStructure()->getStructuresSubstituees())==0; });
        $etablissements = array_filter($etablissements, function (Etablissement $etablissement) { return $etablissement->getStructure()->getSigle() != "NU";});

        return new ViewModel([
            'anomaliesTables' => $anomaliesTables,
            'etablissements' => $etablissements,
        ]);
    }
}