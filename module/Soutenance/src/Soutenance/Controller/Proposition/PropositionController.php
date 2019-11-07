<?php

namespace Soutenance\Controller\Proposition;

use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\FichierThese;
use Application\Entity\Db\Individu;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\VersionFichier;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\Membre;
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
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\SignaturePresident\SiganturePresidentPdfExporter;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class PropositionController extends AbstractController {
    use MembreServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use JustificatifServiceAwareTrait;
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
        /** @var These $these */
        $these = $this->requestedThese();

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        if (!$proposition) {
            $proposition = new Proposition();
            $proposition->setThese($these);
            $this->getPropositionService()->create($proposition);

            /** @var Acteur[] $encadrements */
            $encadrements = $these->getEncadrements();
            foreach ($encadrements as $encadrement) {
                $this->getMembreService()->createMembre($proposition, $encadrement);
            }
            $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
        }

        /** @var Utilisateur $currentUser */
        $currentUser = $this->userContextService->getDbUser();
        $currentIndividu = $currentUser->getIndividu();
        /** @var Role $currentRole */
        $currentRole = $this->userContextService->getSelectedIdentityRole();

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */

        $justificatifsDeposes = $proposition->getJustificatifs();

        $justificatifs = [];

        /**
         * Justificatifs liés à la nature de la thèse ou de la soutenance :
         * - NatureFichier::CODE_DELOCALISATION_SOUTENANCE,
         * - NatureFichier::CODE_DEMANDE_LABEL,
         * - NatureFichier::CODE_DEMANDE_CONFIDENT,
         * - NatureFichier::CODE_LANGUE_ANGLAISE
         */
        if ($proposition->isExterieur()) {
            $justificatifs[] = [
                'type' => NatureFichier::CODE_DELOCALISATION_SOUTENANCE,
                'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_DELOCALISATION_SOUTENANCE, null),
            ];
        }
        if ($proposition->isLabelEuropeen()) {
            $justificatifs[] = [
                'type' => NatureFichier::CODE_DEMANDE_LABEL,
                'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_DEMANDE_LABEL, null),
            ];
        }
        if ($proposition->getThese()->getDateFinConfidentialite() !== null) {
            $justificatifs[] = [
                'type' => NatureFichier::CODE_DEMANDE_CONFIDENT,
                'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_DEMANDE_CONFIDENT, null),
            ];
        }
        if ($proposition->isManuscritAnglais() OR $proposition->isSoutenanceAnglais()) {
            $justificatifs[] = [
                'type' => NatureFichier::CODE_LANGUE_ANGLAISE,
                'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_LANGUE_ANGLAISE, null),
            ];
        }

        /**
         * Justificatifs liés aux membres du jury :
         * - NatureFichier::CODE_DELEGUATION_SIGNATURE,
         * - NatureFichier::CODE_JUSTIFICATIF_HDR,
         * - NatureFichier::CODE_JUSTIFICATIF_EMERITAT
         * @var Membre $membre
         */
        foreach ($proposition->getMembres() as $membre) {
            if ($membre->isVisio()) {
                $justificatifs[] = [
                    'type' => NatureFichier::CODE_DELEGUATION_SIGNATURE,
                    'membre' => $membre,
                    'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_DELEGUATION_SIGNATURE, $membre),
                ];
            }
            if ($membre->getQualite()->getHDR() === 'O') {
                $justificatifs[] = [
                    'type' => NatureFichier::CODE_JUSTIFICATIF_HDR,
                    'membre' => $membre,
                    'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_JUSTIFICATIF_HDR, $membre),
                ];
            }
            if ($membre->getQualite()->getEmeritat() === 'O') {
                $justificatifs[] = [
                    'type' => NatureFichier::CODE_JUSTIFICATIF_EMERITAT,
                    'membre' => $membre,
                    'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_JUSTIFICATIF_EMERITAT, $membre),
                ];
            }
        }

        $justificatifsOk = true;
        foreach ($justificatifs as $justificatif) {
            if ($justificatif['justificatif'] === null) {
                $justificatifsOk = false;
                break;
            }
        }

        return new ViewModel([
            'these'             => $these,
            'proposition'       => $proposition,
            'doctorant'         => $these->getDoctorant(),
            'directeurs'        => $these->getEncadrements(false),
            'validations'       => $this->getPropositionService()->getValidationSoutenance($these),
            'validationActeur'  => $this->getPropositionService()->isValidated($these, $currentIndividu, $currentRole),
            'roleCode'          => $currentRole,
            'indicateurs'       => $this->getPropositionService()->computeIndicateur($proposition),
            'juryOk'            => $this->getPropositionService()->juryOk($proposition),
            'isOk'              => $this->getPropositionService()->isOk($proposition),
            'urlFichierThese'   => $this->urlFichierThese(),
            'justificatifs'     => $justificatifs,
            'justificatifsOk'   => $justificatifsOk,
        ]);
    }

    public function modifierDateLieuAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var DateLieuForm $form */
        $form = $this->getDateLieuForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-date-lieu', ['these' => $idThese], [], true));

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getPropositionService()->annulerValidations($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'form'              => $form,
            'title'             => 'Renseigner la date et le lieu de la soutenance',
        ]);
        return $vm;
    }

    public function modifierMembreAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var MembreForm $form */
        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-membre', ['these' => $these->getId()], [], true));

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = null;
        if ($idMembre) $membre = $this->getMembreService()->find($idMembre);
        else           {
            $membre = new Membre();
            $membre->setProposition($proposition);
        }
        $form->bind($membre);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($idMembre)  {
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
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        if ($membre) {
            $this->getMembreService()->delete($membre);
            $this->getPropositionService()->annulerValidations($proposition);
        }
        $this->redirect()->toRoute('soutenance/proposition',['these' => $idThese],[],true);
    }

    public function labelEuropeenAction()
    {
        /** @var These $these */
        $these = $this->requestedThese();

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var LabelEuropeenForm  $form */
        $form = $this->getLabelEuropeenForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/label-europeen', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getPropositionService()->annulerValidations($proposition);
            }
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
        /** @var These $these */
        $these = $this->requestedThese();

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var LabelEuropeenForm  $form */
        $form = $this->getAnglaisForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/anglais', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getPropositionService()->annulerValidations($proposition);
            }
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
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var ConfidentialiteForm  $form */
        $form = $this->getConfidentialiteForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/confidentialite', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getPropositionService()->annulerValidations($proposition);
            }
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
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var ConfidentialiteForm  $form */
        $form = $this->getChangementTitreForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/changement-titre', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getPropositionService()->annulerValidations($proposition);
            }
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
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

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

        $this->redirect()->toRoute('soutenance/proposition',['these' => $idThese],[],true);

    }

    public function validerStructureAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

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

        $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function refuserStructureAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
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
        /** @var These $these */
        $these = $this->requestedThese();
        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        $encadrement = $these->getEncadrements();
        $codirecteurs = array_filter($encadrement, function(Acteur $a) { return ($a->getRole()->getCode() === Role::CODE_CODIRECTEUR_THESE);});

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
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Acteur[] $directeurs */
        $directeurs = $these->getEncadrements(false);

        /** @var Membre[] $rapporteurs */
        $rapporteurs = ($proposition)?$proposition->getRapporteurs():[];

        return new ViewModel([
            'these'             => $these,
            'proposition'       => $proposition,
            'jury'              => $this->getPropositionService()->juryOk($proposition),
            'validations'       => ($proposition)?$this->getPropositionService()->getValidationSoutenance($these):[],
            'directeurs'        => $directeurs,
            'rapporteurs'       => $rapporteurs,
        ]);
    }

    public function ajouterJustificatifAction() {

        /** @var These $these */
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

        return new ViewModel([
            'these' => $these,
            'form' => $form,
            'membres' => $proposition->getMembres(),
        ]);
    }

    public function retirerJustificatifAction() {

        $these = $this->requestedThese();
        $justifcatif = $this->getJustificatifService()->getRequestedJustificatif($this);
        $this->getJustificatifService()->delete($justifcatif);

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    public function toggleSursisAction() {
        /** @var These $these */
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $sursis = $proposition->hasSursis();
        $proposition->setSurcis(! $sursis);
        $this->getPropositionService()->update($proposition);

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }
}