<?php

namespace Soutenance\Controller\Proposition;

use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\VersionFichier;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Parametre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\Anglais\AnglaisFormAwareTrait;
use Soutenance\Form\ChangementTitre\ChangementTitreFormAwareTrait;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\Confidentialite\ConfidentialiteFormAwareTrait;
use Soutenance\Form\DateLieu\DateLieuForm;
use Soutenance\Form\DateLieu\DateLieuFormAwareTrait;
use Soutenance\Form\Justificatif\JustificatifFormAwareTrait;
use Soutenance\Form\LabelEuropeen\LabelEuropeenForm;
use Soutenance\Form\LabelEuropeen\LabelEuropeenFormAwareTrait;
use Soutenance\Form\Membre\MembreForm;
use Soutenance\Form\Membre\MembreFromAwareTrait;
use Soutenance\Form\Refus\RefusForm;
use Soutenance\Form\Refus\RefusFormAwareTrait;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\SignaturePresident\SiganturePresidentPdfExporter;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Form\Form;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class PropositionController extends AbstractController {
    use ActeurServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use DateLieuFormAwareTrait;
    use MembreFromAwareTrait;
    use LabelEuropeenFormAwareTrait;
    use AnglaisFormAwareTrait;
    use ConfidentialiteFormAwareTrait;
    use RefusFormAwareTrait;
    use ChangementTitreFormAwareTrait;
    use JustificatifFormAwareTrait;

    public function propositionAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        if (!$proposition) {
            $proposition = new Proposition($these);
            //TODO transferer l'etat dans la création
            $proposition->setEtat($this->getPropositionService()->getPropositionEtatByCode(Etat::EN_COURS));
            $this->getPropositionService()->create($proposition);

            $this->getPropositionService()->addDirecteursAsMembres($proposition);
            return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
        }

        /** @var Utilisateur $currentUser */
        $currentUser = $this->userContextService->getDbUser();
        $currentIndividu = $currentUser->getIndividu();
        /** @var Role $currentRole */
        $currentRole = $this->userContextService->getSelectedIdentityRole();

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition);
        $justificatifsOk = $this->getJustificatifService()->isJustificatifsOk($proposition, $justificatifs);

        return new ViewModel([
            'these'             => $these,
            'proposition'       => $proposition,
            'doctorant'         => $these->getDoctorant(),
            'directeurs'        => $this->getActeurService()->getRepository()->findEncadrementThese($these),
            'validations'       => $this->getPropositionService()->getValidationSoutenance($these),
            'validationActeur'  => $this->getPropositionService()->isValidated($these, $currentIndividu, $currentRole),
            'roleCode'          => $currentRole,
            'indicateurs'       => $this->getPropositionService()->computeIndicateur($proposition),
            'juryOk'            => $this->getPropositionService()->juryOk($proposition),
            'isOk'              => $this->getPropositionService()->isOk($proposition),
            'urlFichierThese'   => $this->urlFichierThese(),
            'justificatifs'     => $justificatifs,
            'justificatifsOk'   => $justificatifsOk,

            'FORMULAIRE_DELOCALISATION'              => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_DELOCALISATION)->getValeur(),
            'FORMULAIRE_DELEGUATION'                 => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_DELEGUATION)->getValeur(),
            'FORMULAIRE_DEMANDE_LABEL'               => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_LABEL_EUROPEEN)->getValeur(),
            'FORMULAIRE_DEMANDE_ANGLAIS'             => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_THESE_ANGLAIS)->getValeur(),
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE'     => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_CONFIDENTIALITE)->getValeur(),

        ]);
    }

    public function modifierDateLieuAction() {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var DateLieuForm $form */
        $form = $this->getDateLieuForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-date-lieu', ['these' => $these->getId()], [], true));

        /** @var Proposition $proposition */

        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->updateAndUnvalidate($request, $form, $proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'form'              => $form,
            'title'             => 'Renseigner la date et le lieu de la soutenance',
        ]);
        return $vm;
    }

    public function modifierMembreAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var MembreForm $form */
        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-membre', ['these' => $these->getId()], [], true));

        $new = false;
        $membre = $this->getMembreService()->getRequestedMembre($this);
        if ($membre === null) {
            $membre = new Membre();
            $membre->setProposition($proposition);
            $new = true;
        }
        $form->bind($membre);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($new !== true)  {
                    $this->getMembreService()->update($membre);
                }
                else {
                    $this->getMembreService()->create($membre);
                }
                $this->getPropositionService()->annulerValidations($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setVariables([
            'form'                  => $form,
            'these'                 => $these,
            'title'                 => 'Renseigner les informations sur un membre du jury',
        ]);
        return $vm;
    }

    public function effacerMembreAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        if ($membre) {
            $this->getMembreService()->delete($membre);
            $this->getPropositionService()->annulerValidations($proposition);
        }

        return $this->redirect()->toRoute('soutenance/proposition',['these' => $these->getId()],[],true);
    }

    public function labelEuropeenAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var LabelEuropeenForm  $form */
        $form = $this->getLabelEuropeenForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/label-europeen', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->updateAndUnvalidate($request, $form, $proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title'             => 'Renseignement d\'un label europeen',
            'form'              => $form,
        ]);
        return $vm;
    }

    public function anglaisAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var LabelEuropeenForm  $form */
        $form = $this->getAnglaisForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/anglais', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->updateAndUnvalidate($request, $form, $proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title'             => 'Renseignement de l\'utilisation de l\'anglais',
            'form'              => $form,
        ]);
        return $vm;
    }

    public function confidentialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var ConfidentialiteForm  $form */
        $form = $this->getConfidentialiteForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/confidentialite', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->updateAndUnvalidate($request, $form, $proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title'             => 'Renseignement des informations relatives à la confidentialité',
            'form'              => $form,
        ]);
        return $vm;
    }

    public function changementTitreAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var ConfidentialiteForm  $form */
        $form = $this->getChangementTitreForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/changement-titre', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->updateAndUnvalidate($request, $form, $proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title'             => 'Changement du titre de la thèse',
            'form'              => $form,
        ]);
        return $vm;
    }

    public function validerActeurAction() {
        $these = $this->requestedThese();

        $validation = $this->getValidationService()->validatePropositionSoutenance($these);
        $this->getNotifierSoutenanceService()->triggerValidationProposition($these, $validation);

        /** @var Doctorant $doctorant */
        $doctorant = $these->getDoctorant();

        /** @var Acteur[] $acteurs */
        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray(), [ $doctorant ]);

        $allValidated = true;
        foreach ($acteurs as $acteur) {
            if ($this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $acteur->getIndividu()) === null) {
                $allValidated = false;
                break;
            }
        }
        if ($allValidated) $this->getNotifierSoutenanceService()->triggerNotificationUniteRechercheProposition($these);

        return $this->redirect()->toRoute('soutenance/proposition',['these' => $these->getId()],[],true);

    }

    public function validerStructureAction() {
        $these = $this->requestedThese();

        /**
         * @var Role $role
         * @var Individu $individu
         */
        $role =  $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        switch($role->getCode()) {
            case Role::CODE_UR :
                $this->getValidationService()->validateValidationUR($these, $individu);
                $this->getNotifierSoutenanceService()->triggerNotificationEcoleDoctoraleProposition($these);
                break;
            case Role::CODE_ED :
                $this->getValidationService()->validateValidationED($these, $individu);
                $this->getNotifierSoutenanceService()->triggerNotificationBureauDesDoctoratsProposition($these);
                break;
            case Role::CODE_BDD :
                $this->getValidationService()->validateValidationBDD($these, $individu);
                $this->getNotifierSoutenanceService()->triggerNotificationPropositionValidee($these);
                $this->getNotifierSoutenanceService()->triggerNotificationPresoutenance($these);
                break;
            default :
                throw new RuntimeException("Le role [".$role->getCode()."] ne peut pas valider cette proposition.");
                break;
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function refuserStructureAction() {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var RefusForm $form */
        $form = $this->getRefusForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/refuser-structure', ['these' => $these->getId()], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null) {
                $this->getPropositionService()->annulerValidations($proposition);
                $currentUser = $this->userContextService->getIdentityIndividu();
                $currentRole = $this->userContextService->getSelectedIdentityRole();
                $this->getNotifierSoutenanceService()->triggerRefusPropositionSoutenance($these, $currentUser, $currentRole, $data['motif']);
            }
        }

        return new ViewModel([
            'title'             => "Motivation du refus de la proposition de soutenance",
            'form'              => $form,
            'these'             => $these,
        ]);
    }

    /** Document pour la signature en présidence */
    public function signaturePresidenceAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $codirecteurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE);

        /* @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('view_renderer');

        $exporter = new SiganturePresidentPdfExporter($renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'validations' => $this->getPropositionService()->getValidationSoutenance($these),
            'logos'       => $this->getPropositionService()->getLogos($these),
            'libelle'     => $this->getPropositionService()->generateLibelleSignaturePresidence($these),
            'nbCodirecteur' => count($codirecteurs),
        ]);
        $exporter->export('export.pdf');
        exit;
    }

    public function avancementAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Acteur[] $directeurs */
        $directeurs = $this->getActeurService()->getRepository()->findEncadrementThese($these);

        /** @var Membre[] $rapporteurs */
        $rapporteurs = ($proposition)?$proposition->getRapporteurs():[];

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition);

        return new ViewModel([
            'these'             => $these,
            'proposition'       => $proposition,
            'jury'              => $this->getPropositionService()->juryOk($proposition),
            'justificatif'      => $this->getJustificatifService()->isJustificatifsOk($proposition, $justificatifs),
            'validations'       => ($proposition)?$this->getPropositionService()->getValidationSoutenance($these):[],
            'directeurs'        => $directeurs,
            'rapporteurs'       => $rapporteurs,
        ]);
    }

    public function ajouterJustificatifAction() {

        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $justificatif = new Justificatif();
        $justificatif->setProposition($proposition);
        $form = $this->getJustificatifForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/ajouter-justificatif', ['these' => $these->getId()], [], true));
        $form->bind($justificatif);
        $form->init();

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];

            $form->setData($data);
            if ($form->isValid()) {
                $nature = $this->fichierTheseService->fetchNatureFichier($data['nature']);
                $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->fichierTheseService->createFichierThesesFromUpload($these, $files, $nature, $version);
                $justificatif->setFichier($fichiers[0]);
                $this->getJustificatifService()->create($justificatif);
                return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
            }
        }

        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition);

        return new ViewModel([
            'these' => $these,
            'form' => $form,
            'justificatifs' => $justificatifs,

            'FORMULAIRE_DELOCALISATION'              => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_DELOCALISATION)->getValeur(),
            'FORMULAIRE_DELEGUATION'                 => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_DELEGUATION)->getValeur(),
            'FORMULAIRE_DEMANDE_LABEL'               => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_LABEL_EUROPEEN)->getValeur(),
            'FORMULAIRE_DEMANDE_ANGLAIS'             => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_THESE_ANGLAIS)->getValeur(),
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE'     => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_CONFIDENTIALITE)->getValeur(),
        ]);
    }

    public function retirerJustificatifAction() {

        $these = $this->requestedThese();
        $justifcatif = $this->getJustificatifService()->getRequestedJustificatif($this);
        $this->getJustificatifService()->delete($justifcatif);

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    public function toggleSursisAction() {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $sursis = $proposition->hasSursis();
        $proposition->setSurcis(! $sursis);
        $this->getPropositionService()->update($proposition);

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    /**
     * @param Request $request
     * @param Form $form
     * @param Proposition $proposition
     * @return Proposition
     */
    private function updateAndUnvalidate(Request $request, Form $form, Proposition $proposition) {
        $data = $request->getPost();
        $form->setData($data);
        if ($form->isValid()) {
            $this->getPropositionService()->update($proposition);
            $this->getPropositionService()->annulerValidations($proposition);
        }
        return $proposition;
    }
}