<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\MailConfirmation;
use Application\Service\MailConfirmationServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Depot\Entity\Db\WfEtape;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;

class TheseController extends AbstractController
{
    use TheseServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use MailConfirmationServiceAwareTrait;
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

    public function detailIdentiteAction()
    {
        $these = $this->requestedThese();
        $etablissement = $these->getEtablissement();

        $validationsDesCorrectionsEnAttente = null;
        if ($these->getCorrectionAutorisee() && $these->getPresidentJury()) {
            $validationsDesCorrectionsEnAttente = $this->depotValidationService->getValidationsAttenduesPourCorrectionThese($these);
        }

        $individu = $these->getDoctorant()->getIndividu();
        $mailConfirmation = $this->mailConfirmationService->fetchMailConfirmationsForIndividu($individu);

        $mailContact = null;
        $etatMailContact = null;

        switch(true) {
            case($mailConfirmation && $mailConfirmation->estEnvoye()) :
                $mailContact = $mailConfirmation->getEmail();
                $etatMailContact = MailConfirmation::ENVOYE;
                break;
            case($mailConfirmation && $mailConfirmation->estConfirme()) :
                $mailContact = $mailConfirmation->getEmail();
                $etatMailContact = MailConfirmation::CONFIRME;
                break;
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

        //TODO JP remplacer dans modifierPersopassUrl();
        $urlModification = $this->url()->fromRoute('doctorant/modifier-email-contact',['back' => 1, 'doctorant' => $these->getDoctorant()->getId()], [], true);

        $view = new ViewModel([
            'these'                     => $these,
            'etablissement'             => $etablissement,
            'estDoctorant'              => (bool)$this->userContextService->getSelectedRoleDoctorant(),
            'modifierPersopassUrl'      => $urlModification,
            'modifierCorrecAutorUrl'    => $this->urlDepot()->modifierCorrecAutoriseeForceeUrl($these),
            'accorderSursisCorrecUrl'   => $this->urlDepot()->accorderSursisCorrecUrl($these),
            'nextStepUrl'               => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::PSEUDO_ETAPE_FINALE,
            ]),
            'mailContact'               => $mailContact,
            'etatMailContact'           => $etatMailContact,
            'rattachements'             => $rattachements,
            'validationsDesCorrectionsEnAttente' => $validationsDesCorrectionsEnAttente,
            'utilisateurs'              => $utilisateurs,
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