<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Individu;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Privilege\SoutenancePrivileges;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
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
    use ValidationServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;

    public function engagementImpartialiteAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_VISUALISER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à visualiser l'engagement d'impartialité de cette thèse.");
        }

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        /** @var Individu $individu */
        $individu = $membre->getIndividu();

        /** @var Validation $validation */
        $validation = current($this->validationService->getRepository()->findValidationByCodeAndIndividu(
            TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE,
            $individu
        ));

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'membre' => $membre,
            'validation' => $validation,
            'urlSigner' => $this->url()->fromRoute('soutenance/presoutenance/engagement-impartialite/signer', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true),
            'urlAnnuler' => $this->url()->fromRoute('soutenance/presoutenance/engagement-impartialite/annuler', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true),
        ]);
    }

    public function notifierRapporteursEngagementImpartialiteAction()
    {

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_NOTIFIER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à notifier la demande de signature de l'engagement d'impartialité de cette thèse.");
        }

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            if (($membre->getRole() === Membre::RAPPORTEUR OR $membre->getRole() === Membre::RAPPORTEUR_ABSENT) AND $membre->getIndividu()) {
                $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndIndividu(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $membre->getIndividu());
                if (!$validations) $this->getNotifierSoutenanceService()->triggerDemandeSignatureEngagementImpartialite($these, $proposition, $membre);
            }
        }

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function notifierEngagementImpartialiteAction()
    {

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_NOTIFIER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à notifier la demande de signature de l'engagement d'impartialité de cette thèse.");
        }

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        $this->getNotifierSoutenanceService()->triggerDemandeSignatureEngagementImpartialite($these, $proposition, $membre);

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function signerEngagementImpartialiteAction()
    {

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_SIGNER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à signer cet engagement d'impartialité.");
        }

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);
        /** @var Individu $individu */
        $individu = $membre->getIndividu();
        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        $this->getValidationService()->signEngagementImpartialite($these, $individu);
        $this->getNotifierSoutenanceService()->triggerSignatureEngagementImpartialite($these, $proposition, $membre);

        $this->redirect()->toRoute('soutenance/presoutenance/engagement-impartialite', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
    }


    public function annulerEngagementImpartialiteAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_ANNULER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à annuler la signature de cet engagement d'impartialité.");
        }

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);
        /** @var Individu $individu */
        $individu = $membre->getIndividu();
        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Validation[] $validations */
        $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndIndividu(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $individu);
        $this->getValidationService()->unsignEngagementImpartialite(current($validations));
        $this->getNotifierSoutenanceService()->triggerAnnulationEngagementImpartialite($these, $proposition, $membre);

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }
}