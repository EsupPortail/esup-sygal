<?php

namespace Indicateur\Controller;

use Application\Service\AnomalieServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndicateurController extends AbstractActionController {
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use AnomalieServiceAwareTrait;

    public function indexAction()
    {
        $acteursSansMail = $this->getIndividuService()->getActeurSansMail();
        $theses = $this->getTheseService()->getTheseEnCoursPostSoutenance();
        $anomalies = $this->getAnomalieService()->getAnomalies();

        return new ViewModel([
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
    public function enCoursPostSoutenanceAction()
    {
        $theses = $this->getTheseService()->getTheseEnCoursPostSoutenance();
        return new ViewModel([
                'theses' => null,
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

