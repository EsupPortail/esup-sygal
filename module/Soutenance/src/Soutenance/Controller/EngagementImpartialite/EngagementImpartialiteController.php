<?php

namespace Soutenance\Controller\EngagementImpartialite;

use Application\Entity\Db\These;
use Application\Entity\Db\Validation;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class SoutenanceController
 * @package Soutenance\Controller
 *
 * Controlleur principale du module de gestion de la soutenance
 * @method boolean isAllowed($resource, $privilege = null)
 */

class EngagementImpartialiteController extends AbstractActionController
{
    use TheseServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use MembreServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;

    public function engagementImpartialiteAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        /** @var Validation $validation */
        $validation = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($membre);

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'membre' => $membre,
            'validation' => $validation,
            'urlSigner' => $this->url()->fromRoute('soutenance/engagement-impartialite/signer', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true),
            'urlAnnuler' => $this->url()->fromRoute('soutenance/engagement-impartialite/annuler', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true),
        ]);
    }

    public function notifierRapporteursEngagementImpartialiteAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            if ($membre->getActeur() AND $membre->estRapporteur()) {
                $validation = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($membre);
                if (!$validation) $this->getNotifierSoutenanceService()->triggerDemandeSignatureEngagementImpartialite($these, $proposition, $membre);
            }
        }

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function notifierEngagementImpartialiteAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        if ($membre->getActeur()) {
            $this->getNotifierSoutenanceService()->triggerDemandeSignatureEngagementImpartialite($these, $proposition, $membre);
        }

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function signerEngagementImpartialiteAction()
    {

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        $this->getEngagementImpartialiteService()->createEngagementImpartialite($membre);
        $this->getNotifierSoutenanceService()->triggerSignatureEngagementImpartialite($these, $proposition, $membre);
        $this->getNotifierSoutenanceService()->triggerDemandeAvisSoutenance($these, $proposition, $membre);

        $this->redirect()->toRoute('soutenance/engagement-impartialite', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
    }


    public function annulerEngagementImpartialiteAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Validation[] $validations */
        $this->getEngagementImpartialiteService()->deleteEngagementImpartialite($membre);
        $this->getNotifierSoutenanceService()->triggerAnnulationEngagementImpartialite($these, $proposition, $membre);

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }
}