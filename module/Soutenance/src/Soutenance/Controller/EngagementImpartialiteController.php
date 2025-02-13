<?php

namespace Soutenance\Controller;

use Acteur\Service\ActeurThese\ActeurTheseService;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Doctorant\Entity\Db\Doctorant;
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
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;
use Validation\Entity\Db\ValidationThese;

/**
 * Class SoutenanceController
 * @package Soutenance\Controller
 */

class EngagementImpartialiteController extends AbstractSoutenanceController
{
    use EngagementImpartialiteServiceAwareTrait;
    use HorodatageServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use RenduServiceAwareTrait;
    use TemplateVariablePluginManagerAwareTrait;

    public function engagementImpartialiteAction() : ViewModel
    {
        $this->initializeFromType(false);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($membre);

        if($this->entity->getApprenant() instanceof Doctorant){
            $apprenantTemplateVariable = $this->getDoctorantTemplateVariable($this->entity->getApprenant());
            $vars = [
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
            ];
            $texteEngagnement = $this->getRenduService()->generateRenduByTemplateCode(TexteTemplates::SOUTENANCE_THESE_ENGAGEMENT_IMPARTIALITE, $vars);
        }else{
            $apprenantTemplateVariable = $this->getCandidatTemplateVariable($this->entity->getApprenant());
            $vars = [
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
            ];
            $texteEngagnement = $this->getRenduService()->generateRenduByTemplateCode(TexteTemplates::SOUTENANCE_HDR_ENGAGEMENT_IMPARTIALITE, $vars);
        }

        $validation = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($this->entity, $membre);
        if ($validation === null) $validation = $this->getEngagementImpartialiteService()->getRefusEngagementImpartialiteByMembre($this->entity, $membre);

        return new ViewModel([
            'object' => $this->entity,
            'proposition' => $this->proposition,
            'membre' => $membre,
            'validation' => $validation,
            'encadrants' => $this->acteurService instanceof ActeurTheseService ?
                $this->acteurService->getRepository()->findEncadrementThese($this->entity) :
                $this->acteurService->getRepository()->findEncadrementHDR($this->entity),
            'urlSigner' => $this->url()->fromRoute("soutenance_{$this->type}/engagement-impartialite/signer", ['id' => $this->entity->getId(), 'membre' => $membre->getId()], [], true),
            'urlRefuser' => $this->url()->fromRoute("soutenance_{$this->type}/engagement-impartialite/refuser", ['id' => $this->entity->getId(), 'membre' => $membre->getId()], [], true),
            'urlAnnuler' => $this->url()->fromRoute("soutenance_{$this->type}/engagement-impartialite/annuler", ['id' => $this->entity->getId(), 'membre' => $membre->getId()], [], true),

            'texteEngagement' => $texteEngagnement,
            'typeProposition' => $this->type,
        ]);
    }

    public function notifierEngagementImpartialiteAction()
    {
        $this->initializeFromType(false);
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $acteur = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre);

//        if ($membre->getActeur()) {
        if ($acteur) {
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDemandeSignatureEngagementImpartialite($this->entity, $membre);
                $this->notifierService->trigger($notif);
            } catch (RuntimeException $e) {
                throw new RuntimeException("Aucun mail trouvé pour le rapporteur [".$membre->getDenomination()."]");
            }
        }

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_NOTIFICATION, "Demande de signature de l'engagement d'impartialité");
        $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    public function signerEngagementImpartialiteAction()
    {
        $this->initializeFromType(false);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $signature = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($this->entity, $membre);
        if ($signature === null) {

            $this->getEngagementImpartialiteService()->create($membre, $this->entity);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationSignatureEngagementImpartialite($this->entity, $membre);
                $this->notifierService->trigger($notif);
            } catch (RuntimeException $e) {
                throw new RuntimeException("L'envoi de la notification a échouée. " . $e->getMessage(), null, $e);
            }
        }

        $this->redirect()->toRoute("soutenance_{$this->type}/engagement-impartialite", ['id' => $this->entity->getId(), 'membre' => $membre->getId()], [], true);
    }

    public function refuserEngagementImpartialiteAction()
    {
        $this->initializeFromType(false);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $this->getEngagementImpartialiteService()->createRefus($membre, $this->entity);
        $this->propositionService->annulerValidationsForProposition($this->proposition);
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationRefusEngagementImpartialite($this->entity, $membre);
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Aucun mail trouvé");
        }


        $this->redirect()->toRoute("soutenance_{$this->type}/engagement-impartialite", ['id' => $this->entity->getId(), 'membre' => $membre->getId()], [], true);
    }

    public function annulerEngagementImpartialiteAction()
    {
        $this->initializeFromType(false);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** @var ValidationThese[] $validations */
        $this->getEngagementImpartialiteService()->delete($membre);
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationAnnulationEngagementImpartialite($this->entity, $membre);
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur [".$membre->getDenomination()."]");
        }

        $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }
}