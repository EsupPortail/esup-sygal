<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Intervention;
use Soutenance\Entity\Parametre;
use Soutenance\Service\Intervention\InterventionServiceAwareTrait;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class InterventionController extends AbstractController {
    use EntityManagerAwareTrait;
    use InterventionServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;

    public function indexAction()
    {
        return new ViewModel([]);
    }

    public function afficherAction()
    {
        $these = $this->getTheseService()->getRequestedThese($this);
        $proposition = $this->getPropositionService()->findByThese($these);
        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition, true);
        $distanciels = $this->getInterventionService()->getInterventionByTheseAndType($these, Intervention::TYPE_DISTANCIEL);
        return new ViewModel([
            'these' => $these,
            'distanciel' => (!empty($distanciels))?current($distanciels):null,
            'proposition' => $proposition,
            'justificatifs' => $justificatifs,
            'urlFichierThese' => $this->urlFichierThese(),
            'FORMULAIRE_DELEGUATION' => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_DELEGUATION)->getValeur(),
        ]);
    }

    public function togglePresidentDistancielAction()
    {
        $these = $this->getTheseService()->getRequestedThese($this);
        $interventions = $this->getInterventionService()->getInterventionByTheseAndType($these, Intervention::TYPE_DISTANCIEL);
        $nbInterventions = count($interventions);

        switch ($nbInterventions) {
            case 0: //creation d'une intervention
                $intervention = new Intervention();
                $intervention->setThese($these);
                $intervention->setType(Intervention::TYPE_DISTANCIEL);
                $this->getInterventionService()->create($intervention);
                break;
            case 1: //historisation
                $intervention = current($interventions);
                $this->getInterventionService()->historiser($intervention);
                break;
            default: //erreur
                throw new RuntimeException("Plusieurs Intervention de type '".Intervention::TYPE_DISTANCIEL." pour la thèse '".$these->getId()."'.");
        }

        return $this->redirect()->toRoute('soutenance/intervention/afficher', ['these' => $these->getId()], [], true);
    }
}