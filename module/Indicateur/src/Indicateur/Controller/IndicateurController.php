<?php

namespace Indicateur\Controller;

use Application\Service\AnomalieServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndicateurController extends AbstractActionController {
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use AnomalieServiceAwareTrait;

    public function indexAction()
    {
        $acteursSansMail = $this->getIndividuService()->getActeurSansMail();
        $theses = $this->getTheseService()->getTheseEnCoursPostSoutenance();
        $anomalies = $this->getAnomalieService()->getAnomalies();

        $etablissements = [];
        $etablissements[] = $this->getEtablissementService()->getEtablissementById(2);
        $etablissements[] = $this->getEtablissementService()->getEtablissementById(3);
        $etablissements[] = $this->getEtablissementService()->getEtablissementById(4);
        $etablissements[] = $this->getEtablissementService()->getEtablissementById(5);

        $effectifs = [];
        $effectifs["COMUE"] = $this->getTheseService()->getEffectifs();
        foreach($etablissements as $etablissement) {
            $result = $this->getTheseService()->getEffectifs($etablissement);
            $effectifs[$etablissement->getStructure()->getCode()] = $result;
        }

        return new ViewModel([
                "effectifs" => $effectifs,
                "theses" => $theses,
                "sansMail" => $acteursSansMail,
                "anomalies" => $anomalies,
            ]
        );
    }

    /**
     * faire remonter les thèses ayant en cours ayant une date de soutenance dépassée
     * @return ViewModel
     */
    public function soutenanceDepasseeAction()
    {
        $theses = $this->getTheseService()->getTheseEnCoursPostSoutenance();
        return new ViewModel([
                'theses' => $theses,
            ]
        );
    }

    public function acteurSansMailAction()
    {
        return new ViewModel([
                "sansMail" => null,
            ]
        );
    }
}

