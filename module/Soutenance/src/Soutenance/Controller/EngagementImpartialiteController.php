<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Validation;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Soutenance\Entity\Evenement;
use Soutenance\Entity\Membre;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Evenement\EvenementServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Laminas\View\Model\ViewModel;

/**
 * Class SoutenanceController
 * @package Soutenance\Controller
 */

class EngagementImpartialiteController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use EvenementServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use PropositionServiceAwareTrait;

    public function engagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** @var Validation $validation */
        $validation = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($these, $membre);
        if ($validation === null) $validation = $this->getEngagementImpartialiteService()->getRefusEngagementImpartialiteByMembre($these, $membre);

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'membre' => $membre,
            'validation' => $validation,
            'encadrants' => $this->getActeurService()->getRepository()->findEncadrementThese($these),
            'urlSigner' => $this->url()->fromRoute('soutenance/engagement-impartialite/signer', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true),
            'urlRefuser' => $this->url()->fromRoute('soutenance/engagement-impartialite/refuser', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true),
            'urlAnnuler' => $this->url()->fromRoute('soutenance/engagement-impartialite/annuler', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true),
        ]);
    }

    public function notifierRapporteursEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            if ($membre->getActeur() and $membre->estRapporteur()) {
                $validation = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($these, $membre);
                if (!$validation) $this->getNotifierSoutenanceService()->triggerDemandeSignatureEngagementImpartialite($these, $proposition, $membre);
            }
        }

        $this->getEvenementService()->ajouterEvenement($proposition, Evenement::EVENEMENT_ENGAGEMENT);
        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function notifierEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        if ($membre->getActeur()) {
            $this->getNotifierSoutenanceService()->triggerDemandeSignatureEngagementImpartialite($these, $proposition, $membre);
        }

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function signerEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $signature = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($these, $membre);
        if ($signature === null) {
            $this->getEngagementImpartialiteService()->create($membre, $these);
            $this->getNotifierSoutenanceService()->triggerSignatureEngagementImpartialite($these, $proposition, $membre);
            $this->getNotifierSoutenanceService()->triggerDemandeAvisSoutenance($these, $proposition, $membre);
        }

        $this->redirect()->toRoute('soutenance/engagement-impartialite', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
    }

    public function refuserEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $this->getEngagementImpartialiteService()->createRefus($membre, $these);
        $this->getPropositionService()->annulerValidations($proposition);
        $this->getNotifierSoutenanceService()->triggerRefusEngagementImpartialite($these, $proposition, $membre);


        $this->redirect()->toRoute('soutenance/engagement-impartialite', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
    }

    public function annulerEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** @var Validation[] $validations */
        $this->getEngagementImpartialiteService()->delete($membre);
        $this->getNotifierSoutenanceService()->triggerAnnulationEngagementImpartialite($these, $proposition, $membre);

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }
}