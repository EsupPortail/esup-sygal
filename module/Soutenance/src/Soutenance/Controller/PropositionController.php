<?php

namespace Soutenance\Controller;

use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Information\Service\InformationServiceAwareTrait;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Assertion\HDR\PropositionHDRAssertionAwareTrait;
use Soutenance\Assertion\These\PropositionTheseAssertionAwareTrait;
use Soutenance\Entity\Adresse;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionThese;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceFormAwareTrait;
use Soutenance\Form\Anglais\AnglaisFormAwareTrait;
use Soutenance\Form\Confidentialite\ConfidentialiteFormAwareTrait;
use Soutenance\Form\DateLieu\DateLieuFormAwareTrait;
use Soutenance\Form\Membre\MembreFromAwareTrait;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Adresse\AdresseServiceAwareTrait;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Horodatage\HorodatageServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/** @method boolean isAllowed($resource, $privilege = null) */
abstract class PropositionController extends AbstractSoutenanceController
{
    use AdresseServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use HorodatageServiceAwareTrait;
    use InformationServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use ParametreServiceAwareTrait;
    use RenduServiceAwareTrait;
    use TemplateVariablePluginManagerAwareTrait;

    use AdresseSoutenanceFormAwareTrait;
    use DateLieuFormAwareTrait;
    use MembreFromAwareTrait;
    use AnglaisFormAwareTrait;
    use ConfidentialiteFormAwareTrait;

    use PropositionTheseAssertionAwareTrait;
    use PropositionHDRAssertionAwareTrait;

    private PhpRenderer $renderer;


    public function setRenderer(PhpRenderer $renderer) : void
    {
        $this->renderer = $renderer;
    }
    public function modifierDateLieuAction(): ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->dateLieuForm;
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/modifier-date-lieu", ['id' => $this->entity->getId()], [], true));
        $form->bind($this->proposition);

        $canModifierGestion = $this->isAllowed($this->entity, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION);
        if ($canModifierGestion) {
            $form->setDateHeureRequired(false);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->update($request, $form, $this->proposition);
                $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Date et lieu");
                $this->propositionService->initialisationDateRetour($this->proposition);
                if (!$canModifierGestion) $this->propositionService->annulerValidationsForProposition($this->proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'form' => $form,
            'title' => 'Renseigner la date et le lieu de la soutenance',
        ]);
        return $vm;
    }

    public function modifierMembreAction(): ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/modifier-membre", ['id' => $this->entity->getId()], [], true));

        $new = false;
        $membre = $this->getMembreService()->getRequestedMembre($this);
        if ($membre === null) {
            $membre = new Membre();
            $membre->setProposition($this->proposition);
            $new = true;
        }
        $form->bind($membre);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($new !== true) {
                    $this->getMembreService()->update($membre);
                } else {
                    $this->getMembreService()->create($membre);
                }
                $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Jury");
                if (!$this->isAllowed($this->entity, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->propositionService->annulerValidationsForProposition($this->proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate("soutenance/proposition/modifier-membre");
        $vm->setVariables([
            'form' => $form,
            'proposition' => $this->proposition,
            'title' => 'Renseigner les informations sur un membre du jury',
        ]);
        return $vm;
    }

    public function effacerMembreAction() : ViewModel|Response
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $membre = $this->getMembreService()->getRequestedMembre($this);
        if ($membre) {
            if (!$this->isAllowed($this->entity, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->propositionService->annulerValidationsForProposition($this->proposition);
            $this->getMembreService()->delete($membre);
            $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Jury");
        }

        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
    }

    public function anglaisAction(): ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getAnglaisForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/anglais", ['id' => $this->entity->getId()], [], true));
        $form->bind($this->proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $this->proposition);
            $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Informations complémentaires");
            if (!$this->isAllowed($this->entity, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->propositionService->annulerValidationsForProposition($this->proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Renseignement de l\'utilisation de l\'anglais',
            'form' => $form,
        ]);
        return $vm;
    }

    public function confidentialiteAction(): ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getConfidentialiteForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/confidentialite", ['id' => $this->entity->getId()], [], true));
        $form->bind($this->proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $this->proposition);
            $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Informations complémentaires");
            if (!$this->isAllowed($this->entity, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->propositionService->annulerValidationsForProposition($this->proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/proposition/confidentialite');
        $vm->setVariables([
            'title' => 'Renseignement des informations relatives à la confidentialité',
            'form' => $form,
            'object' => $this->entity,
        ]);
        return $vm;
    }

    public function toggleSursisAction()
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $sursis = $this->proposition->hasSursis();
        $this->proposition->setSursis(!$sursis);
        $this->propositionService->update($this->proposition);

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_ETAT, "Sursis");

        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
    }

    public function refuserStructureAction(): ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_VALIDER_BDD, PropositionPrivileges::PROPOSITION_VALIDER_UR, PropositionPrivileges::PROPOSITION_VALIDER_ED]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getRefusForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/refuser-structure", ['type'=> $this->type, 'id' => $this->entity->getId()], [], true));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null) {
                $this->propositionService->annulerValidationsForProposition($this->proposition);
                $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_VALIDATION, "Structures");

                $currentUser = $this->userContextService->getIdentityIndividu();
                $currentRole = $this->userContextService->getSelectedIdentityRole();
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationRefusPropositionSoutenance($this->entity, $currentUser, $currentRole, $data['motif']);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate("soutenance/proposition/refuser-structure");
        $vm->setVariables([
            'title' => "Motivation du refus de la proposition de soutenance",
            'form' => $form,
        ]);
        return $vm;
    }

    /**
     * @param Request $request
     * @param Form $form
     * @param Proposition $proposition
     * @return Proposition
     */
    protected function update(Request $request, Form $form, Proposition $proposition): Proposition
    {
        $data = $request->getPost();
        $form->setData($data);
        if ($form->isValid()) {
            $this->propositionService->update($proposition);
        }
        return $proposition;
    }

    public function suppressionAction()
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        //detruire la  || historiser si on histo
        $this->propositionService->historise($this->proposition);

        //historiser les validations
        $validations = $this->proposition->getObject() instanceof These ?
            $this->validationService->getRepository()->findValidationsByThese($this->entity) :
            $this->validationService->getRepository()->findValidationsByHDR($this->entity);
        foreach ($validations as $validation) {
            $this->validationService->historiser($validation);
        }

        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationSuppressionProposition($this->entity);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
            // aucun destinataire , todo : cas à gérer !
        }

        if ($redirectUrl = $this->params()->fromQuery('redirect')) {
            return $this->redirect()->toUrl($redirectUrl);
        }
        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
    }

    /** Vue ***********************************************************************************************************/

    public function generateViewDateLieuAction(): ViewModel
    {
        $this->initializeFromType();

        $categorieParametre = $this->proposition instanceof PropositionThese ? SoutenanceParametres::CATEGORIE : \Soutenance\Provider\Parametre\HDR\SoutenanceParametres::CATEGORIE;

        $vm = new ViewModel();
        $vm->setTerminal(true);
        $vm->setTemplate("soutenance/proposition/generate-view-date-lieu");
        $vm->setVariables([
            'proposition' => $this->proposition,
            'FORMULAIRE_DELOCALISATION' => $this->getParametreService()->getValeurForParametre($categorieParametre, SoutenanceParametres::DOC_DELOCALISATION),
            'canModifier' => $this->isAllowed(PropositionPrivileges::getResourceId(PropositionPrivileges::PROPOSITION_MODIFIER)),
            'typeProposition' => $this->type,
        ]);
        return $vm;
    }

    public function generateViewJuryAction(): ViewModel
    {
        $this->initializeFromType();

        /** Indicateurs --------------------------------------------------------------------------------------------- */
        $indicateurs = $this->propositionService->computeIndicateurForProposition($this->proposition);
        $juryOk = $this->propositionService->isJuryPropositionOk($this->proposition, $indicateurs);
        if ($juryOk === false) $indicateurs["valide"] = false;
        //$isOk = $this->propositionService->isOk($this->proposition, $indicateurs);
        $categorieParametre = $this->proposition instanceof PropositionThese ? SoutenanceParametres::CATEGORIE : \Soutenance\Provider\Parametre\HDR\SoutenanceParametres::CATEGORIE;

        $vm = new ViewModel();
        $vm->setTerminal(true);
        $vm->setTemplate("soutenance/proposition/generate-view-jury");
        $vm->setVariables([
            'object' => $this->entity,
            'proposition' => $this->proposition,
            'FORMULAIRE_DELEGUATION' => $this->getParametreService()->getValeurForParametre($categorieParametre, SoutenanceParametres::DOC_DELEGATION_SIGNATURE),
            'canModifier' => $this->isAllowed(PropositionPrivileges::getResourceId(PropositionPrivileges::PROPOSITION_MODIFIER)),
            'indicateurs' => $indicateurs,
        ]);
        return $vm;
    }

    public function generateViewInformationsAction(): ViewModel
    {
        $this->initializeFromType();

        $categorieParametre = $this->proposition instanceof PropositionThese ? SoutenanceParametres::CATEGORIE : \Soutenance\Provider\Parametre\HDR\SoutenanceParametres::CATEGORIE;

        $vm = new ViewModel();
        $vm->setTerminal(true);
        $vm->setVariables([
            'object' => $this->entity,
            'proposition' => $this->proposition,
            'FORMULAIRE_DEMANDE_LABEL' => $this->getParametreService()->getValeurForParametre($categorieParametre, SoutenanceParametres::DOC_LABEL_EUROPEEN),
            'FORMULAIRE_DEMANDE_ANGLAIS' => $this->getParametreService()->getValeurForParametre($categorieParametre, SoutenanceParametres::DOC_REDACTION_ANGLAIS),
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $this->getParametreService()->getValeurForParametre($categorieParametre, SoutenanceParametres::DOC_CONFIDENTIALITE),
            'canModifier' => $this->isAllowed(PropositionPrivileges::getResourceId(PropositionPrivileges::PROPOSITION_MODIFIER)),
        ]);
        return $vm;
    }

    /** Adresse de la soutenance **************************************************************************************/

    public function ajouterAdresseAction(): ViewModel
    {
        $this->initializeFromType();

        $adresse = new Adresse();
        $adresse->setProposition($this->proposition);
        $form = $this->getAdresseSoutenanceForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/ajouter-adresse", ['id' => $this->entity->getId(), 'proposition' => $this->proposition->getId()], [], true));
        $form->bind($adresse);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getAdresseService()->create($adresse);
                exit();
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout de l'adresse exacte de soutenance",
            'form' => $form,
        ]);
        $vm->setTemplate('soutenance/default/default-form');
        return $vm;
    }

    public function modifierAdresseAction(): ViewModel
    {
        $this->initializeFromType();
        $adresse = $this->getAdresseService()->getRequestedAdresse($this);
        $form = $this->getAdresseSoutenanceForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/modifier-adresse", ['id' => $this->entity->getId(), 'adresse' => $adresse->getId()], [], true));
        $form->bind($adresse);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getAdresseService()->update($adresse);
                exit();
            }
        }

        $vm = new ViewModel([
            'title' => "Modification de l'adresse exacte de soutenance",
            'form' => $form,
        ]);
        $vm->setTemplate('soutenance/default/default-form');
        return $vm;
    }

    public function historiserAdresseAction(): Response
    {
        $this->initializeFromType();

        $adresse = $this->getAdresseService()->getRequestedAdresse($this);
        $this->getAdresseService()->historise($adresse);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
    }

    public function restaurerAdresseAction(): Response
    {
        $this->initializeFromType();

        $adresse = $this->getAdresseService()->getRequestedAdresse($this);
        $this->getAdresseService()->restore($adresse);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
    }

    public function supprimerAdresseAction(): ViewModel
    {
        $this->initializeFromType();
        $adresse = $this->getAdresseService()->getRequestedAdresse($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getAdresseService()->delete($adresse);
            exit();
        }

        $vm = new ViewModel();
        if ($adresse !== null) {
            $vm->setTemplate('soutenance/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de l'adresse exacte",
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute("soutenance_{$this->type}/proposition/supprimer-adresse", ['id' => $this->entity->getId(), "adresse" => $adresse->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function demanderAdresseAction(): Response
    {
        $this->initializeFromType();

        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationDemandeAdresse($this->proposition);
            if (empty($notif->getTo())) {
                throw new RuntimeException(
                    "Aucune adresse mail trouvée pour les aspects Doctorat de l'établissement d'inscription '{$this->entity->getEtablissement()}'");
            }
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
            // aucun destinataire , todo : cas à gérer !
        }

        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    /** Error *********************************************************************************************************/

    /**
     * @param Proposition $proposition
     * @param array $privilieges
     * @param string|null $message
     * @return ViewModel|null
     */
    protected function verifierAutorisation(Proposition $proposition, array $privilieges, ?string $message = null): ?ViewModel
    {
        $authorized = false;
        $propositionAssertion = $proposition->getObject() instanceof These ?
            $this->getPropositionTheseAssertion() :
            $this->getPropositionHDRAssertion();
        foreach ($privilieges as $priviliege) {
            $authorized = $propositionAssertion->computeValeur(null, $proposition, $priviliege);
            if ($authorized === true) break;
        }
        if ($authorized === false) {
            $vm = new ViewModel();
            $vm->setTemplate('soutenance/error/403');
            $vm->setVariables(['message' => $message]);
            return $vm;
        }
        return null;
    }

    /** Gestion des horodatages d'une proposition **************************/

    public function horodatagesAction() : ViewModel
    {
        $this->initializeFromType();

        $message = "Vous n'êtes pas autorisé·e à visualiser les horodatages concernant cette proposition de soutenance.";
        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_VISUALISER], $message);
        if ($autorisation !== null) return $autorisation;

        $horodatages = $this->proposition->getHorodatages();

        $vm = new ViewModel();
        $vm->setTemplate("soutenance/proposition/horodatages");
        $vm->setVariables([
            'proposition' => $this->proposition,
            'horodatages' => $horodatages,
        ]);
        return $vm;
    }
}