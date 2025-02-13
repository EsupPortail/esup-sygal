<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Service\MailConfirmationServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Validation\Service\ValidationThese\ValidationTheseServiceAwareTrait;
use Depot\Entity\Db\WfEtape;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Doctorant\Service\MissionEnseignement\MissionEnseignementServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;

class TheseController extends AbstractController
{
    use TheseServiceAwareTrait;
    use ValidationTheseServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use MailConfirmationServiceAwareTrait;
    use MissionEnseignementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    /**
     * @see TheseRechercheController::indexAction()
     */
    public function indexAction(): Response
    {
        return $this->redirect()->toRoute('these/recherche', [], [], true);
    }

    public function detailIdentiteAction(): ViewModel
    {
        $these = $this->requestedThese();
        $etablissement = $these->getEtablissement();

        $validationsDesCorrectionsEnAttente = null;
        if ($these->getCorrectionAutorisee() && $these->getPresidentJury()) {
            $validationsDesCorrectionsEnAttente = $this->depotValidationService->getValidationsAttenduesPourCorrectionThese($these);
        }

        $unite = $these->getUniteRecherche();
        $rattachements = [];
        if ($unite !== null) {
            $rattachements = $this->getUniteRechercheService()->findEtablissementRattachement($unite);
        }

        $utilisateurs = [];
        foreach ($these->getActeurs() as $acteur) {
            $utilisateursTrouves = $this->utilisateurService->getRepository()->findByIndividu($acteur->getIndividu()); // ok
            $utilisateurs[$acteur->getId()] = $utilisateursTrouves;
        }

        $view = new ViewModel([
            'these' => $these,
            'etablissement' => $etablissement,
            'estDoctorant' => (bool)$this->userContextService->getSelectedRoleDoctorant(),
            'modifierEmailContactUrl' => $this->urlDoctorant()->modifierEmailContactUrl($these->getDoctorant(), true),
            'modifierEmailContactConsentUrl' => $this->urlDoctorant()->modifierEmailContactConsentUrl(
                $these->getDoctorant(),
                $this->url()->fromRoute(null, [], [], true)),
            'modifierCorrecAutorUrl' => $this->urlDepot()->modifierCorrecAutoriseeForceeUrl($these),
            'accorderSursisCorrecUrl' => $this->urlDepot()->accorderSursisCorrecUrl($these),
            'nextStepUrl' => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::PSEUDO_ETAPE_FINALE,
            ]),
            'rattachements' => $rattachements,
            'validationsDesCorrectionsEnAttente' => $validationsDesCorrectionsEnAttente,
            'utilisateurs' => $utilisateurs,
            'missions' => $this->getMissionEnseignementService()->getRepository()->findByDoctorant($these->getDoctorant()),
        ]);
        $view->setTemplate('these/these/identite');

        return $view;
    }

    /**
     * Import forcé d'une thèse et des inf.
     */
    public function refreshTheseAction()
    {
        throw new \BadMethodCallException("Cette action n'est plus fonctionnelle !");
    }
}