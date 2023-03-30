<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Validation;
use Laminas\View\Model\ViewModel;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Provider\Template\TexteTemplates;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Horodatage\HorodatageServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/**
 * Class SoutenanceController
 * @package Soutenance\Controller
 */

class EngagementImpartialiteController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use HorodatageServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use PropositionServiceAwareTrait;
    use RenduServiceAwareTrait;

    public function engagementImpartialiteAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $vars = [ 'membre' => $membre, 'doctorant' => $these->getDoctorant() ];
        $texteEngagnement = $this->getRenduService()->generateRenduByTemplateCode(TexteTemplates::SOUTENANCE_ENGAGEMENT_IMPARTIALITE, $vars);

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

            'texteEngagement' => $texteEngagnement,
        ]);
    }

    public function notifierRapporteursEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            if ($membre->getActeur() and $membre->estRapporteur()) {
                $validation = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($these, $membre);
                if (!$validation) {
                    try {
                        $notif = $this->soutenanceNotificationFactory->createNotificationDemandeSignatureEngagementImpartialite($these, $membre);
                        $this->notifierService->trigger($notif);
                    } catch (RuntimeException $e) {
                        throw new RuntimeException("Aucun mail trouvé pour le rapporteur [".$membre->getDenomination()."]");
                    }
                }
            }
        }
        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_NOTIFICATION, "Demande de signature de l'engagement d'impartialité");
        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function notifierEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        if ($membre->getActeur()) {
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDemandeSignatureEngagementImpartialite($these, $membre);
                $this->notifierService->trigger($notif);
            } catch (RuntimeException $e) {
                throw new RuntimeException("Aucun mail trouvé pour le rapporteur [".$membre->getDenomination()."]");
            }
        }

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_NOTIFICATION, "Demande de signature de l'engagement d'impartialité");
        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function signerEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $signature = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($these, $membre);
        if ($signature === null) {
            $this->getEngagementImpartialiteService()->create($membre, $these);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationSignatureEngagementImpartialite($these, $membre);
                $this->notifierService->trigger($notif);
            } catch (RuntimeException $e) {
                throw new RuntimeException("Aucun mail trouvé pour le rapporteur [".$membre->getDenomination()."]");
            }
        }

        $this->redirect()->toRoute('soutenance/engagement-impartialite', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
    }

    public function refuserEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $this->getEngagementImpartialiteService()->createRefus($membre, $these);
        $this->getPropositionService()->annulerValidationsForProposition($proposition);
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationRefusEngagementImpartialite($these, $membre);
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Aucun mail trouvé");
        }


        $this->redirect()->toRoute('soutenance/engagement-impartialite', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
    }

    public function annulerEngagementImpartialiteAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** @var Validation[] $validations */
        $this->getEngagementImpartialiteService()->delete($membre);
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationAnnulationEngagementImpartialite($these, $membre);
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur [".$membre->getDenomination()."]");
        }

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }
}