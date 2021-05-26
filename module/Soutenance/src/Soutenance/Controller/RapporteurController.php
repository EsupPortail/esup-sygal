<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RapporteurController extends AbstractActionController {
    use ActeurServiceAwareTrait;
    use AvisServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use MembreServiceAwareTrait;
    use TheseServiceAwareTrait;

    public function indexAction()
    {
        $these = $this->getTheseService()->getRequestedThese($this);
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $clef = $this->params()->fromQuery('clef');

        $valide = ($this->getMembreService()->verifierClef($membre, $clef) AND $membre->getActeur() !== null);

        $encadrants = $this->getActeurService()->getRepository()->findEncadrementThese($these);
        $engagement = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($these, $membre);
        if ($engagement === null) $engagement = $this->getEngagementImpartialiteService()->getRefusEngagementImpartialiteByMembre($these, $membre);
        $avis = $this->getAvisService()->getAvisByMembre($membre);

        return new ViewModel([
            'these' => $these,
            'membre' => $membre,
            'clef' => $clef,

            'valide' => $valide,

            'encadrants' => $encadrants,
            'engagement' => $engagement,
            'avis' => $avis,
        ]);
    }
}