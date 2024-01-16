<?php

namespace Depot\Controller;

use Application\Command\Exception\TimedOutCommandException;
use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Variable;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Service\MailConfirmationServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Depot\Entity\Db\Attestation;
use Depot\Entity\Db\Diffusion;
use Depot\Entity\Db\FichierThese;
use Depot\Entity\Db\MetadonneeThese;
use Depot\Entity\Db\RdvBu;
use Depot\Entity\Db\WfEtape;
use Depot\Event\EventsInterface;
use Depot\Form\Attestation\AttestationTheseForm;
use Depot\Form\ConformiteFichierForm;
use Depot\Form\Diffusion\DiffusionTheseForm;
use Depot\Form\Metadonnees\MetadonneeTheseForm;
use Depot\Form\PointsDeVigilanceForm;
use Depot\Form\RdvBuTheseDoctorantForm;
use Depot\Form\RdvBuTheseForm;
use Depot\Service\FichierThese\Exception\ValidationImpossibleException;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Depot\Service\Notification\DepotNotificationFactoryAwareTrait;
use Depot\Service\These\Convention\ConventionPdfExporter;
use Depot\Service\These\DepotServiceAwareTrait;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Depot\Service\Workflow\WorkflowServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Laminas\EventManager\Event;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\InputFilter\InputFilter;
use Laminas\Stdlib\ParametersInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareTrait;
use UnicaenApp\Traits\MessageAwareInterface;

class DepotController extends AbstractController
{
    use DepotServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use MessageCollectorAwareTrait;
    use DepotNotificationFactoryAwareTrait;
    use NotifierServiceAwareTrait;
    use RoleServiceAwareTrait;
    use TheseServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use WorkflowServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use EtablissementServiceAwareTrait;
    use EntityManagerAwareTrait;
    use MailConfirmationServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    private $timeoutRetraitement;

    /**
     * @var \Depot\Form\RdvBuTheseForm
     */
    private $rdvBuTheseDoctorantForm;

    /**
     * @var \Depot\Form\RdvBuTheseDoctorantForm
     */
    private $rdvBuTheseForm;

    /**
     * @var \Depot\Form\Attestation\AttestationTheseForm
     */
    private $attestationTheseForm;

    /**
     * @var \Depot\Form\Diffusion\DiffusionTheseForm
     */
    private $diffusionTheseForm;

    /**
     * @var MetadonneeTheseForm
     */
    private $metadonneeTheseForm;

    /**
     * @var \Depot\Form\PointsDeVigilanceForm
     */
    private $pointsDeVigilanceForm;

    /**
     * @var PhpRenderer
     */
    private $renderer;

    /**
     * @param \Depot\Form\RdvBuTheseDoctorantForm $rdvBuTheseDoctorantForm
     */
    public function setRdvBuTheseDoctorantForm(RdvBuTheseDoctorantForm $rdvBuTheseDoctorantForm)
    {
        $this->rdvBuTheseDoctorantForm = $rdvBuTheseDoctorantForm;
    }

    /**
     * @param \Depot\Form\RdvBuTheseForm $rdvBuTheseForm
     */
    public function setRdvBuTheseForm(RdvBuTheseForm $rdvBuTheseForm)
    {
        $this->rdvBuTheseForm = $rdvBuTheseForm;
    }

    /**
     * @param AttestationTheseForm $attestationTheseForm
     */
    public function setAttestationTheseForm(AttestationTheseForm $attestationTheseForm)
    {
        $this->attestationTheseForm = $attestationTheseForm;
    }

    /**
     * @param DiffusionTheseForm $diffusionTheseForm
     */
    public function setDiffusionTheseForm(DiffusionTheseForm $diffusionTheseForm)
    {
        $this->diffusionTheseForm = $diffusionTheseForm;
    }

    /**
     * @param MetadonneeTheseForm $metadonneeTheseForm
     */
    public function setMetadonneeTheseForm(MetadonneeTheseForm $metadonneeTheseForm)
    {
        $this->metadonneeTheseForm = $metadonneeTheseForm;
    }

    /**
     * @param PointsDeVigilanceForm $pointsDeVigilanceForm
     */
    public function setPointsDeVigilanceForm(PointsDeVigilanceForm $pointsDeVigilanceForm)
    {
        $this->pointsDeVigilanceForm = $pointsDeVigilanceForm;
    }

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Action servant l'accueil du menu Dépôt :
     * - pour un doctorant / directeur affiche la liste des thèses en cours
     * - pour un bu et mdd la liste des thèses en cours dans son établissement
     * - sinon un message disant de sélectionner une thèse via l'annuaire
     **/
    public function depotAccueilAction()
    {
        /** @var Role $role */
        $role = $this->userContextService->getSelectedIdentityRole();

        if ($these = $this->requestedThese()) {
            $theses = [$these];
        } else {
            $user = $this->userContextService->getIdentityDb();

            $codeRole = $role ? $role->getCode() : null;
            $theses = [];
            switch ($codeRole) {
                case Role::CODE_DOCTORANT :
                    $theses = $this->getTheseService()->getRepository()->findThesesByDoctorantAsIndividu($user->getIndividu());
                    break;
                case Role::CODE_DIRECTEUR_THESE :
                case Role::CODE_CODIRECTEUR_THESE :
                    $theses = $this->getTheseService()->getRepository()->findThesesByActeur($user->getIndividu(), $role);
                    break;
                default :
                    break;
            }
        }

        return new ViewModel([
            'role' => $role,
            'theses' => $theses,
        ]);
    }

    public function roadmapAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these' => $these,
        ]);
        $view->setTemplate('depot/depot/roadmap');

        return $view;
    }

    public function detailDescriptionAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these'                  => $these,
            'modifierMetadonneesUrl' => $this->urlDepot()->modifierMetadonneesUrl($these),
            'nextStepUrl'            => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::CODE_SIGNALEMENT_THESE,
                WfEtape::PSEUDO_ETAPE_FINALE,
            ]),
        ]);
        $view->setTemplate('depot/depot/description');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function validationPageDeCouvertureAction()
    {
        $these = $this->requestedThese();

        $validation = current($this->validationService->getRepository()->findValidationByCodeAndThese(
            TypeValidation::CODE_PAGE_DE_COUVERTURE,
            $these
        ));

        $informations = $this->theseService->fetchInformationsPageDeCouverture($these);

        $view = new ViewModel([
            'these'            => $these,
            'validation'       => $validation ?: null,
            'apercevoirPdcUrl' => $this->urlFichierThese()->apercevoirPageDeCouverture($these),
            'refreshTheseUrl'  => $this->urlThese()->refreshTheseUrl($these, $this->urlDepot()->validationPageDeCouvertureUrl($these)),
            'validerUrl'       => $this->urlDepot()->validerPageDeCouvertureUrl($these),
            'devaliderUrl'     => $this->urlDepot()->devaliderPageDeCouvertureUrl($these),
            'nextStepUrl'      => $this->urlWorkflow()->nextStepBox($these, null, [WfEtape::CODE_VALIDATION_PAGE_DE_COUVERTURE]),
            'informations'     => $informations,
            'msgCollector'     => $this->getServiceMessageCollector(),
        ]);

        return $view;
    }

    public function detailDepotAction()
    {
        return $this->detailDepotActionViewModel(false);
    }

    public function detailDepotVersionCorrigeeAction()
    {
        return $this->detailDepotActionViewModel(true);
    }

    /**
     * @param bool $versionCorrigee
     * @return ViewModel
     */
    private function detailDepotActionViewModel($versionCorrigee = false)
    {
        $these = $this->requestedThese();

        $codeVersion = $versionCorrigee ?
            VersionFichier::CODE_ORIG_CORR :
            VersionFichier::CODE_ORIG;

        $view = new ViewModel([
            'these'            => $these,
            'versionCorrigee'  => $versionCorrigee,
            'theseUrl'         => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_THESE_PDF, $codeVersion),
            'annexesUrl'       => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_FICHIER_NON_PDF, $codeVersion),
            'attestationUrl'   => $this->urlDepot()->attestationThese($these, $codeVersion),
            'diffusionUrl'     => $this->urlDepot()->diffusionThese($these, $codeVersion),
            'nextStepUrl'      => $this->urlWorkflow()->nextStepBox($these, null, [
                $versionCorrigee ? WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE : WfEtape::CODE_DEPOT_VERSION_ORIGINALE,
                $versionCorrigee ? WfEtape::CODE_ATTESTATIONS_VERSION_CORRIGEE : WfEtape::CODE_ATTESTATIONS,
                $versionCorrigee ? WfEtape::CODE_AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE : WfEtape::CODE_AUTORISATION_DIFFUSION_THESE,
                WfEtape::PSEUDO_ETAPE_FINALE,
            ]),
        ]);

        $view->setTemplate('depot/depot/depot');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function detailDepotDiversAction(): ViewModel
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these' => $these,
            'pvSoutenanceUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_PV_SOUTENANCE),
            'rapportSoutenanceUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_RAPPORT_SOUTENANCE),
            'preRapportSoutenanceUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE),
            'demandeConfidentUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_DEMANDE_CONFIDENT),
            'prolongConfidentUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_PROLONG_CONFIDENT),
            'convMiseEnLigneUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_CONV_MISE_EN_LIGNE),
            'convMiseEnLigneAvenantUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_CONV_MISE_EN_LIGNE_AVENANT),
            'charteDoctoratUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_CHARTE_DOCTORAT),
            'charteDoctoratAvenantUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_CHARTE_DOCTORAT_AVENANT),
            'convFormationDoctoraleUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_CONV_FORMATION_DOCTORALE),
            'convFormationDoctoraleAvenantUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_CONV_FORMATION_DOCTORALE_AVENANT),
        ]);
        $view->setTemplate('depot/depot/depot-divers');

        return $view;
    }

    public function detailArchivageAction()
    {
        $view = $this->detailArchivageActionViewModel(false);

        return $view;
    }

    public function detailArchivageVersionCorrigeeAction()
    {
        $view = $this->detailArchivageActionViewModel(true);

        return $view;
    }

    /**
     * @param bool $versionCorrigee
     * @return Response|ViewModel
     */
    private function detailArchivageActionViewModel($versionCorrigee = false)
    {
        $these = $this->requestedThese();

        $version = $versionCorrigee ?
            VersionFichier::CODE_ORIG_CORR :
            VersionFichier::CODE_ORIG;

        $theseFichiers = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, false);
        $fichierThese = current($theseFichiers);

        if (!$fichierThese) {
            return $this->redirect()->toUrl($this->urlDepot()->depotThese($these));
        }

        if ($this->getRequest()->isPost()) {
            $action = $this->params()->fromPost('action', $this->params()->fromQuery('action'));
            if ('tester' === $action) {
                try {
                    $this->fichierTheseService->validerFichierThese($fichierThese);
                }
                catch (ValidationImpossibleException $vie) {
                    // Le test d'archivabilité du fichier '%s' a rencontré un problème indépendant de notre volonté
                }
            }
            return $this->redirect()->refresh();
        }

        $theseFichiersItems = array_map(function (FichierThese $fichier) use ($these) {
            return [
                'file'        => $fichier,
                'downloadUrl' => $this->urlFichierThese()->telechargerFichierThese($these, $fichier),
            ];
        }, $theseFichiers);

        $theseFichiersRetraites = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, true);
        $fichierTheseRetraite = current($theseFichiersRetraites);

        $theseRetraiteeUrl = $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_THESE_PDF, $version, true);

        $view = new ViewModel([
            'these'                              => $these,
            'fichierThese'                       => $fichierThese,
            'fichierTheseRetraite'               => $fichierTheseRetraite,
            'theseFichiersItems'                 => $theseFichiersItems,
            'theseRetraiteeUrl'                  => $theseRetraiteeUrl,
            'testArchivabiliteTheseOriginaleUrl' => $this->urlDepot()->testArchivabilite($these, $version),
            'archivabiliteTheseRetraiteeUrl'     => $this->urlDepot()->archivabiliteThese($these, $version, true),
            'conformiteTheseRetraiteeUrl'        => $this->urlDepot()->conformiteTheseRetraitee($these, $version),
            'nextStepUrl'                        => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::CODE_ARCHIVABILITE_VERSION_ORIGINALE,
                WfEtape::CODE_ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE,
                WfEtape::CODE_ARCHIVABILITE_VERSION_ARCHIVAGE,
                WfEtape::CODE_ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE,
                WfEtape::CODE_DEPOT_VERSION_ARCHIVAGE,
                WfEtape::CODE_DEPOT_VERSION_ARCHIVAGE_CORRIGEE,
                WfEtape::CODE_VERIFICATION_VERSION_ARCHIVAGE,
                WfEtape::CODE_VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE,
                WfEtape::PSEUDO_ETAPE_FINALE,
            ]),
        ]);
        $view->setTemplate('depot/depot/archivage');

        return $view;
    }

    public function detailRdvBuAction()
    {
        $estDoctorant = (bool) $this->userContextService->getSelectedRoleDoctorant();
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
        $asynchronous = $this->params()->fromRoute('asynchronous');

        $versionArchivable = $this->fichierTheseService->getRepository()->getVersionArchivable($these);
        $hasVA = $this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_ARCHI);
        $hasVD = $this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_DIFF);

        $validationsPdc = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $these);
        $pageCouvValidee = !empty($validationsPdc);

        $isExemplPapierFourniPertinent = $this->depotService->isExemplPapierFourniPertinent($these);

        $view = new ViewModel([
            'these'        => $these,
            'diffusion'    => $these->getDiffusionForVersion($version),
            'estDoctorant' => $estDoctorant,
            'modifierUrl'  => $this->urlDepot()->modifierRdvBuUrl($these),
            'validerUrl'   => $this->urlDepot()->validerRdvBuUrl($these),
            'devaliderUrl' => $this->urlDepot()->devaliderRdvBuUrl($these),
            'validation'   => $these->getValidation(TypeValidation::CODE_RDV_BU),
            'msgCollector' => $this->getServiceMessageCollector(),
            'nextStepUrl'  => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::CODE_RDV_BU_SAISIE_DOCTORANT,
                WfEtape::CODE_RDV_BU_VALIDATION_BU,
            ]),
            'versionArchivable' => $versionArchivable,
            'hasVA' => $hasVA,
            'hasVD' => $hasVD,
            'pageCouvValidee' => $pageCouvValidee,
            'asynchronous' => $asynchronous,
            'isExemplPapierFourniPertinent' => $isExemplPapierFourniPertinent,

        ]);

        $view->setTemplate('depot/depot/rdv-bu' . ($estDoctorant ? '-doctorant' : null));

        return $view;
    }

    public function modifierCorrectionAutoriseeForceeAction()
    {
        $these = $this->requestedThese();
        $form = $this->getCorrectionAutoriseeForceeForm($these);

        if ($this->getRequest()->isPost()) {
            /** @var ParametersInterface $post */
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                $forcage = $post->get('forcage') ?: null;
                $this->depotService->updateCorrectionAutoriseeForcee($these, $forcage);
            }
        }
        else {
            $form->get('forcage')->setValue($these->getCorrectionAutoriseeForcee() ?: '');
        }

        $form->setAttribute('action', $this->urlDepot()->modifierCorrecAutoriseeForceeUrl($these));

        return new ViewModel([
            'these' => $these,
            'form' => $form,
            'title' => "Forçage du témoin de corrections attendues",
        ]);
    }

    private function getCorrectionAutoriseeForceeForm(These $these)
    {
        $isCorrectionAutoriseeFromImport = $these->isCorrectionAutorisee(false);
        $valeurCorrectionAutoriseeFromImport = $these->getCorrectionAutorisee(false);

        $radioOptions = [
            These::$CORRECTION_AUTORISEE_FORCAGE_AUCUNE      => "Forcer à &laquo; <strong>Aucune correction attendue</strong> &raquo;.",
            These::$CORRECTION_AUTORISEE_FORCAGE_FACULTATIVE => "Forcer à &laquo; <strong>Corrections facultatives attendues</strong> &raquo;.",
            These::$CORRECTION_AUTORISEE_FORCAGE_OBLIGATOIRE => "Forcer à &laquo; <strong>Corrections obligatoires attendues</strong> &raquo;.",
        ];

        if ($isCorrectionAutoriseeFromImport) {
            $correctionAttendueImportee = sprintf("Corrections %s attendues", strtolower($these->getCorrectionAutoriseeToString(true, false)));
            unset($radioOptions[$valeurCorrectionAutoriseeFromImport]);
        } else {
            $correctionAttendueImportee = "Aucune correction attendue";
            unset($radioOptions[These::$CORRECTION_AUTORISEE_FORCAGE_AUCUNE]);
        }

        $radioOptions = array_merge(
            ['' => sprintf("Ne pas forcer et utiliser la valeur importée de %s.", $these->getSource())],
            $radioOptions
        );

        $radio = (new Radio('forcage'))
            ->setValueOptions($radioOptions)
            ->setLabelOption('disable_html_escape', true);

        $message = sprintf(
            "Actuellement, la valeur du témoin de corrections attendues importé de %s <br>est &laquo; <strong>%s</strong> &raquo;. <br>" .
            "Vous avez ici la possibilité d'outre-passer cette valeur importée en réalisant un forçage...",
            $these->getSource(),
            $correctionAttendueImportee
        );

        $form = new Form('correctionAutoriseeForcee');
        $form->setLabel($message);
        $form->add($radio);
        $form->add((new Submit('submit'))->setValue("Enregistrer"));

        $form->setInputFilter((new InputFilter())->getFactory()->createInputFilter([
            'forcage' => [
                'allow_empty' => true
            ]
        ]));

        return $form;
    }

    /**
     * Accord d'un sursis pour le dépôt de la version corrigée.
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function accorderSursisCorrectionAction(): ViewModel
    {
        $result = $this->confirm()->execute();

        $these = $this->requestedThese();
        $dateButoirDepotVersionCorrigeeAvecSursis = $these->computeDateButoirDepotVersionCorrigeeAvecSursis($these->getDateSoutenance());

        // si un tableau est retourné par le plugin Confirm, l'opération a été confirmée
        if (is_array($result)) {
            $this->depotService->updateSursisDateButoirDepotVersionCorrigee($these, $dateButoirDepotVersionCorrigeeAvecSursis);

            // déclenchement d'un événement
            $event = new Event(EventsInterface::EVENT__SURSIS_CORRECTION_ACCORDE, $these);
            $this->events->triggerEvent($event);
            $this->flashMessengerAddMessagesFromEvent($event);
        }

        $viewModel = $this->confirm()->getViewModel();
        $viewModel->setVariables([
            'title'   => "Sursis",
            'date' => $dateButoirDepotVersionCorrigeeAvecSursis,
        ]);

        return $viewModel;
    }

    public function modifierRdvBuAction(): Response|ViewModel
    {
        $these = $this->requestedThese();
        $estDoctorant = (bool) $this->userContextService->getSelectedRoleDoctorant();
        $isExemplPapierFourniPertinent = $this->depotService->isExemplPapierFourniPertinent($these);

        $validationsPdc = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $these);
        $pageCouvValidee = !empty($validationsPdc);

        $rdvBu = $these->getRdvBu() ?: new RdvBu($these);
        $rdvBu->setVersionArchivableFournie($this->fichierTheseService->getRepository()->existeVersionArchivable($these));

        /** @var \Depot\Form\RdvBuTheseForm|\Depot\Form\RdvBuTheseDoctorantForm $form */
        $form = $estDoctorant ? $this->rdvBuTheseDoctorantForm : $this->rdvBuTheseForm;
        $form->bind($rdvBu);

        // Attestations remplies ?
        $isAttestationsSaisies = $this->depotService->isAttestationsVersionInitialeSaisies($these);
        $form->get('attestationsRemplies')->setValue($isAttestationsSaisies);

        if ($form instanceof RdvBuTheseForm && ! $this->depotService->isExemplPapierFourniPertinent($these)) {
            $form->disableExemplPapierFourni();
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $post['attestationsRemplies'] = $isAttestationsSaisies; // pour que le formulaire soit valide
            $form->setData($post);
            if ($form->isValid()) {
                /** @var RdvBu $formRdvBu */
                $formRdvBu = $form->getData();
                $inserting = $formRdvBu->getId() === null;
                $this->depotService->updateRdvBu($these, $formRdvBu);

                $this->flashMessenger()->addMessage("Informations enregistrées avec succès.", 'success');
                if ($message = $this->depotService->getMessage('<br>', MessageAwareInterface::SUCCESS)) {
                    $this->flashMessenger()->addMessage($message, 'rdv_bu/success');
                }
                if ($message = $this->depotService->getMessage('<br>', MessageAwareInterface::INFO)) {
                    $this->flashMessenger()->addMessage($message, 'rdv_bu/info');
                }

                // notification par mail à la BU quand le doctorant saisit les infos pour la 1ere fois
                if ($estDoctorant && $inserting) {
                    $notif = $this->depotNotificationFactory->createNotificationForRdvBuSaisiParDoctorant($these, true);
                    $result = $this->notifierService->trigger($notif);
                    $result->feedFlashMessenger($this->flashMessenger(), 'rdv_bu/');
                }

                if (! $this->getRequest()->isXmlHttpRequest()) {
                    return $this->redirect()->toRoute('these/rdv-bu', [], [], true);
                }
            }
        }

        $form->setAttribute('action', $this->urlDepot()->modifierRdvBuUrl($these));

        $vm = new ViewModel([
            'these' => $these,
            'form'  => $form,
            'title' => "Rendez-vous avec la bibliothèque universitaire",
            'pageCouvValidee' => $pageCouvValidee,
            'isExemplPapierFourniPertinent' => $isExemplPapierFourniPertinent,
        ]);

        $vm->setTemplate('depot/depot/modifier-rdv-bu' . ($estDoctorant ? '-doctorant' : null));

        return $vm;
    }

    public function validationTheseCorrigeeAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG_CORR);

        $isRemiseExemplairePapierRequise = $this->depotService->isRemiseExemplairePapierRequise($these, $version);

        $hasVAC = $this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_ARCHI_CORR);
        $hasVDC = $this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_DIFF_CORR);

        $view = new ViewModel([
            'these'                           => $these,
            'validationDepotTheseCorrigeeUrl' => $this->urlDepot()->validationDepotTheseCorrigeeUrl($these),
            'validationCorrectionTheseUrl'    => $this->urlDepot()->validationCorrectionTheseUrl($these),
            'nextStepUrl'                     => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT,
                WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR,
            ], [
                'message' => $isRemiseExemplairePapierRequise ?
                    "Il ne reste plus qu'à fournir à la bibliothèque universitaire un exemplaire imprimé de la version corrigée pour valider le dépôt." :
                    null,
            ]),
            'hasVAC' => $hasVAC,
            'hasVDC' => $hasVDC,
            'isDoctorant' => ($this->userContextService->getSelectedRoleDoctorant()),

        ]);

        return $view;
    }

    public function validerFichierAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these' => $these,
            'testerFichierUrl' => $this->urlDepot()->modifierMetadonneesUrl($these),
        ]);
        $view->setTemplate('depot/depot/archivage');

        return $view;
    }

    public function theseAction()
    {
        $these = $this->requestedThese();
        $estCorrige = (bool) $this->params()->fromQuery('corrige', false);
        $estExpurge = (bool) $this->params()->fromQuery('expurge', false);
        $inclureValidite = (bool) $this->params()->fromQuery('inclureValidite', false);
        $validerAuto = (bool) $this->params()->fromQuery('validerAuto', false);
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));

        $nature = $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_THESE_PDF);

        $titre = $estExpurge ?
            sprintf("Version %s expurgée pour la diffusion", $estCorrige ? "corrigée" : "") :
            sprintf("Thèse %s au format PDF", $estCorrige ? "corrigée" : "");

        $form = $this->uploader()->getForm();
        $form->setAttribute('id', uniqid('form-'));
//        $form->addElement((new Hidden('annexe'))->setValue(0));
        $form->addElement((new Hidden('nature'))->setValue($this->idify($nature)));
        $form->addElement((new Hidden('version'))->setValue($this->idify($version)));
        if ($validerAuto) {
            $form->addElement((new Hidden('validerAuto'))->setValue(1));
        }

        $view = new ViewModel([
            'titre'          => $titre,
            'these'          => $these,
            'info'           => $estExpurge ? "<strong>NB</strong>: rassembler la thèse sur un seul fichier au format PDF." : null,
            'uploadUrl'      => $this->urlFichierThese()->televerserFichierThese($these),
            'theseListUrl'   => $this->urlFichierThese()->listerFichiers($these, $nature, $version, false, ['inclureValidite' => $inclureValidite]),
            'nature'         => $nature,
            'versionFichier' => $version,
        ]);
        $view->setTemplate('depot/depot/depot/these');

        return $view;
    }

    /**
     * - Affichage de la thèse retraitée.
     * - Retraitement automatique de la thèse
     *
     * NB: En fonction de la propriété 'timeoutRetraitement', un timeout peut être appliqué au lancement du
     * script de retraitement. Si ce timout est atteint, l'exécution du script est interrompue
     * et une exception TimedOutCommandException est levée.
     *
     * @return ViewModel|Response
     * @throws OptimisticLockException
     */
    public function theseRetraiteeAction()
    {
        $these = $this->requestedThese();
        $nature = $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_THESE_PDF);
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));

        $versionOriginale = $this->fichierTheseService->fetchVersionFichier(
            $version->estVersionCorrigee() ? VersionFichier::CODE_ORIG_CORR : VersionFichier::CODE_ORIG
        );
        $versionArchivage = $this->fichierTheseService->fetchVersionFichier(
            $version->estVersionCorrigee() ? VersionFichier::CODE_ARCHI_CORR : VersionFichier::CODE_ARCHI
        );

        /** @var FichierThese $fichierVersionOriginale */
        $fichierVersionOriginale = current($this->fichierTheseService->getRepository()->fetchFichierTheses($these, $nature, $versionOriginale, false));
        /** @var FichierThese $fichierVersionArchivage */
        $fichierVersionArchivage = current($this->fichierTheseService->getRepository()->fetchFichierTheses($these, $nature, $versionArchivage,true)) ?: null;

        $form = $this->uploader()->getForm();
        $form->setAttribute('id', uniqid('form-'));
        $form->addElement((new Hidden('validerAuto'))->setValue(1));
        $form->addElement((new Hidden('retraitement'))->setValue(FichierThese::RETRAITEMENT_MANU));
        $form->addElement((new Hidden('nature'))->setValue($this->idify($nature)));
        $form->addElement((new Hidden('version'))->setValue($this->idify($versionArchivage)));

        if ($this->getRequest()->isPost()) {
            if ('creerVersionRetraitee' === $this->params()->fromQuery('action')) {
                try {
                    // Un timeout peut être appliqué au lancement du  script de retraitement.
                    // Si ce timout est atteint, l'exécution du script est interrompue
                    // et une exception TimedOutCommandException est levée.
                    $timeout = $this->timeoutRetraitement;
                    $fichierVersionArchivage = $this->fichierTheseService->creerFichierTheseRetraite($fichierVersionOriginale, $timeout);
                    try {
                        $this->fichierTheseService->validerFichierThese($fichierVersionArchivage);
                    } catch (ValidationImpossibleException $vie) {
                        // Le test d'archivabilité du fichier '%s' a rencontré un problème indépendant de notre volonté
                    }
                } catch (TimedOutCommandException $toce) {
                    // relancer le retraitement en tâche de fond
                    $this->fichierTheseService->creerFichierTheseRetraiteAsync($fichierVersionOriginale);
                }
            }
            return $this->redirect()->toRoute(null, [], ['query'=>$this->params()->fromQuery()], true);
        }

        $theseRetraiteeAutoListUrl = $this->urlFichierThese()->listerFichiers(
            $these, $nature, $versionArchivage, FichierThese::RETRAITEMENT_AUTO, ['inclureValidite' => true]);
        $theseRetraiteeManuListUrl = $this->urlFichierThese()->listerFichiers(
            $these, $nature, $versionArchivage, FichierThese::RETRAITEMENT_MANU, ['inclureValidite' => true]);

        $view = new ViewModel([
            'these'                     => $these,
            'info'                      => null,
            'uploadUrl'                 => $this->urlFichierThese()->televerserFichierThese($these),
            'theseRetraiteeAutoListUrl' => $theseRetraiteeAutoListUrl,
            'theseRetraiteeManuListUrl' => $theseRetraiteeManuListUrl,
//            'creerVersionRetraiteeUrl'  => $this->url()->fromRoute(null, [], ['query' => ['action' => 'creerVersionRetraitee']], true),
            'creerVersionRetraiteeUrl'  => $this->urlDepot()->creerVersionRetraitee($these, $versionArchivage),
            'fichierVOouVOC'            => $fichierVersionOriginale,
            'fichierVAouVAC'            => $fichierVersionArchivage,
            'nature'                    => $nature,
            'versionFichier'            => $versionArchivage,
        ]);
        $view->setTemplate('depot/depot/archivage/these-retraitee');

        return $view;
    }

    public function annexesAction()
    {
        $these = $this->requestedThese();
        $estCorrige = (bool) $this->params()->fromQuery('corrige', false);
        $estExpurge = (bool) $this->params()->fromQuery('expurge', false);
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));
        $nature = $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_FICHIER_NON_PDF);

        $hasFichierThese = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, false));
        $hasFichiersAnnexesThese = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_FICHIER_NON_PDF, $version, false));

        $titre = $estExpurge ?
            sprintf("Fichiers %s expurgés hors PDF", $estCorrige ? "corrigés" : "") :
            sprintf("Fichiers %s hors PDF", $estCorrige ? "corrigés" : "");

        $form = $this->uploader()->getForm();
        $form->setAttribute('id', uniqid('form-'));
        $form->addElement((new Hidden('annexe'))->setValue(1));
        $form->addElement((new Hidden('nature'))->setValue($this->idify($nature)));
        $form->addElement((new Hidden('version'))->setValue($this->idify($version)));

        $view = new ViewModel([
            'titre'          => $titre,
            'these'          => $these,
            'uploadUrl'      => $this->urlFichierThese()->televerserFichierThese($these),
            'annexesListUrl' => $this->urlFichierThese()->listerFichiers($these, $nature, $version),
            'nature'         => $nature,
            'versionFichier' => $version,
            'hasFichierThese' => $hasFichierThese,
            'hasFichiersAnnexesThese' => $hasFichiersAnnexesThese,
        ]);
        $view->setTemplate('depot/depot/depot/annexes');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotPvSoutenanceAction(): ViewModel
    {
        $these = $this->requestedThese();
        $dateSoutenanceDepassee = $these->getDateSoutenance() && $these->getDateSoutenance() < new \DateTime();

        $view = $this->createViewForFichierAction(NatureFichier::CODE_PV_SOUTENANCE);
        $view->setVariable('readonly', true);
        $view->setVariable('isVisible', $dateSoutenanceDepassee);
        if (!$dateSoutenanceDepassee) {
            $view->setVariable('message', "Non pertinent avant la soutenance.");
        }
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotRapportSoutenanceAction(): ViewModel
    {
        $these = $this->requestedThese();
        $dateSoutenanceDepassee = $these->getDateSoutenance() && $these->getDateSoutenance() < new \DateTime();

        $view = $this->createViewForFichierAction(NatureFichier::CODE_RAPPORT_SOUTENANCE);
        $view->setVariable('readonly', true);
        $view->setVariable('isVisible', $dateSoutenanceDepassee);
        if (!$dateSoutenanceDepassee) {
            $view->setVariable('message', "Non pertinent avant la soutenance.");
        }
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotPreRapportSoutenanceAction(): ViewModel
    {
        $these = $this->requestedThese();
        $dateSoutenanceDepassee = $these->getDateSoutenance() && $these->getDateSoutenance() < new \DateTime();

        $view = $this->createViewForFichierAction(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
        $view->setVariable('readonly', true);
        $view->setVariable('isVisible', $dateSoutenanceDepassee);
        if (!$dateSoutenanceDepassee) {
            $view->setVariable('message', "Non pertinent avant la soutenance.");
        }
        $view->setVariable('maxUploadableFilesCount', 3);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotDemandeConfidentAction(): ViewModel
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_DEMANDE_CONFIDENT);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotProlongConfidentAction(): ViewModel
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_PROLONG_CONFIDENT);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotConvMiseEnLigneAction(): ViewModel
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_CONV_MISE_EN_LIGNE);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotConvMiseEnLigneAvenantAction(): ViewModel
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_CONV_MISE_EN_LIGNE_AVENANT);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotCharteDoctoratAction(): ViewModel
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_CHARTE_DOCTORAT);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotCharteDoctoratAvenantAction(): ViewModel
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_CHARTE_DOCTORAT_AVENANT);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotConvFormationDoctoraleAction(): ViewModel
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_CONV_FORMATION_DOCTORALE);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotConvFormationDoctoraleAvenantAction(): ViewModel
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_CONV_FORMATION_DOCTORALE_AVENANT);
        $view->setTemplate('depot/depot/depot/fichier-divers');

        return $view;
    }

    /**
     * @param string $codeNatureFichier
     * @return ViewModel
     */
    private function createViewForFichierAction(string $codeNatureFichier): ViewModel
    {
        $these = $this->requestedThese();
        $nature = $this->fichierTheseService->fetchNatureFichier($codeNatureFichier);
        $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);

        if (!$nature) {
            throw new RuntimeException("Nature de fichier introuvable: " . $codeNatureFichier);
        }

        $form = $this->uploader()->getForm();
        $form->setAttribute('id', uniqid('form-'));
//        $form->setUploadMaxFilesize('50M');
        $form->addElement((new Hidden('nature'))->setValue($this->idify($nature)));
        $form->addElement((new Hidden('version'))->setValue($this->idify($version)));
        $form->get('files')->setLabel("")->setAttribute('multiple', false)/*->setAttribute('accept', '.pdf')*/;

        $view = new ViewModel([
            'these'           => $these,
            'uploadUrl'       => $this->urlFichierThese()->televerserFichierThese($these),
            'fichiersListUrl' => $this->urlFichierThese()->listerFichiers($these, $nature),
            'nature'          => $nature,
            'version'         => $version,
        ]);

        return $view;
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testArchivabiliteAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));

        $theseFichiers = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, false);
        /** @var FichierThese $fichierThese */
        $fichierThese = current($theseFichiers);

        if ($this->getRequest()->isPost()) {
            $action = $this->params()->fromPost('action', $this->params()->fromQuery('action'));
            if ('tester' === $action) {
                try {
                    $validite = $this->fichierTheseService->validerFichierThese($fichierThese);

                    // création automatique d'une validation du dépôt de la version corrigée (par le doctorant)
                    if ($validite->getEstValide() && $version->estVersionCorrigee()) {
                        $this->depotValidationService->validateDepotTheseCorrigee($these);
                        $this->depotService->notifierCorrectionsApportees($these);
                    }
                } catch (ValidationImpossibleException $vie) {
                    // Le test d'archivabilité du fichier '%s' a rencontré un problème indépendant de notre volonté
                }
            }

            return $this->redirect()->toUrl($this->urlDepot()->archivageThese($these, $version->getCode()));
        }

        $view = new ViewModel([
            'these'                => $these,
            'fichierThese'         => $fichierThese,
            'testArchivabiliteUrl' => $this->urlDepot()->testArchivabilite($these, $version->getCode()),
        ]);
        $view->setTemplate('depot/depot/archivage/test-archivabilite');

        return $view;
    }

    public function archivabiliteTheseAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));
        $retraite = true;

        $codeVersionRetraitee = $version->estVersionCorrigee() ?
            VersionFichier::CODE_ARCHI_CORR :
            VersionFichier::CODE_ARCHI;

        $theseFichiersRetraite = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $codeVersionRetraitee, true);
        $fichierTheseRetraite = current($theseFichiersRetraite);

        $variableEmailAssist = $this->variableService->getRepository()->findOneByCodeAndThese(Variable::CODE_EMAIL_ASSISTANCE, $these);

        $view = new ViewModel([
            'these'    => $these,
            'fichier'  => $fichierTheseRetraite,
            'retraite' => $retraite,
            'contact'  => $variableEmailAssist->getValeur(),
        ]);
        $view->setTemplate('depot/depot/archivage/archivabilite-these');

        return $view;
    }

    public function conformiteTheseRetraiteeAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));

        $versionArchivage = $this->fichierTheseService->fetchVersionFichier(
            $version->estVersionCorrigee() ? VersionFichier::CODE_ARCHI_CORR : VersionFichier::CODE_ARCHI
        );

        $fichier = current($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $versionArchivage, true)) ?: null;

        $variableEmailAssist = $this->variableService->getRepository()->findOneByCodeAndThese(Variable::CODE_EMAIL_ASSISTANCE, $these);

        $view = new ViewModel([
            'these'                     => $these,
            'fichierTheseRetraite'      => $fichier,
            'validerFichierRetraiteUrl' => $this->urlDepot()->certifierConformiteTheseRetraiteUrl($these, $versionArchivage),
            'contact'                   => $variableEmailAssist->getValeur(),
        ]);
        $view->setTemplate('depot/depot/archivage/conformite-these-retraitee');

        return $view;
    }

    private function isEtapeAttestationVisible(These $these, VersionFichier $version)
    {
        $versionInitialeAtteignable = $this->workflowService->findOneByEtape($these, WfEtape::CODE_ATTESTATIONS)->getAtteignable();
        $versionCorrigeeAtteignable = $this->workflowService->findOneByEtape($these, WfEtape::CODE_ATTESTATIONS_VERSION_CORRIGEE)->getAtteignable();
        return
            $version->estVersionCorrigee() && $versionCorrigeeAtteignable ||
            !$version->estVersionCorrigee() && $versionInitialeAtteignable /*&& !$versionCorrigeeAtteignable*/;
    }

    public function attestationAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));
        $attestation = $these->getAttestationForVersion($version);
        $hasFichierThese = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, false));

        if (! $this->isEtapeAttestationVisible($these, $version)) {
            return false;
        }
        if (! $hasFichierThese) {
            return false;
        }

        $view = new ViewModel([
            'these'                  => $these,
            'version'                => $version,
            'attestation'            => $attestation,
            'form'                   => $this->getAttestationTheseForm($version), // les labels des cases à cocher sont affichés
            'modifierAttestationUrl' => $this->urlDepot()->modifierAttestationUrl($these, $version),
            'hasFichierThese'        => $hasFichierThese,
            'resaisirAttestationsVersionCorrigee' => $this->depotService->getResaisirAttestationsVersionCorrigee(),
        ]);
        $view->setTemplate('depot/depot/attestation');

        return $view;
    }

    public function modifierAttestationAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));
        $attestation = $these->getAttestationForVersion($version);
        $form = $this->getAttestationTheseForm($version);

        if ($attestation === null) {
            $attestation = new Attestation();
            $attestation->setThese($these);
        }

        $form->bind($attestation);

        if ($this->getRequest()->isPost()) {
            /** @var ParametersInterface $post */
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            $isValid = $form->isValid();
            if ($isValid) {
                /** @var Attestation $attestation */
                $attestation = $form->getData();
                $attestation->setVersionCorrigee($version->estVersionCorrigee());

                $this->depotService->updateAttestation($these, $attestation);

                if (! $this->getRequest()->isXmlHttpRequest()) {
                    return $this->redirect()->toRoute('these/depot', [], [], true);
                }
            }
        }

        $form->setAttribute('action', $this->urlDepot()->modifierAttestationUrl($these, $version));

        return new ViewModel([
            'these' => $these,
            'form'  => $form,
            'title' => "Attestations",
        ]);
    }

    /**
     * @param VersionFichier $version
     * @return AttestationTheseForm
     */
    private function getAttestationTheseForm(VersionFichier $version)
    {
        $these = $this->requestedThese();
        $diffusion = $these->getDiffusionForVersion($version);

        /** @var AttestationTheseForm $form */
        $form = $this->attestationTheseForm;

        if ($diffusion && ! $diffusion->isRemiseExemplairePapierRequise()) {
            $form->disableExemplaireImprimeConformeAVersionDeposee();
        }

        return $form;
    }

    private function isEtapeDiffusionVisible(These $these, VersionFichier $version)
    {
        $versionInitialeAtteignable = $this->workflowService->findOneByEtape($these, WfEtape::CODE_AUTORISATION_DIFFUSION_THESE)->getAtteignable();
        $versionCorrigeeAtteignable = $this->workflowService->findOneByEtape($these, WfEtape::CODE_AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE)->getAtteignable();
        return
            $version->estVersionCorrigee() && $versionCorrigeeAtteignable ||
            !$version->estVersionCorrigee() && $versionInitialeAtteignable /*&& !$versionCorrigeeAtteignable*/;
    }

    public function diffusionAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));
        $diffusion = $these->getDiffusionForVersion($version);
        $hasFichierThese = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, false));

        if (! $this->isEtapeDiffusionVisible($these, $version)) {
            return false;
        }
        if (! $hasFichierThese) {
            return false;
        }

        /** @var \Depot\Form\Diffusion\DiffusionTheseForm $form */
        $form = $this->diffusionTheseForm;

        $versionExpurgee = $version->estVersionCorrigee() ? VersionFichier::CODE_DIFF_CORR : VersionFichier::CODE_DIFF;
        $theseFichiersExpurges = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $versionExpurgee, false);
        $annexesFichiersExpurges = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_FICHIER_NON_PDF, $versionExpurgee, false);

        if ($diffusion) {
            $form->bind($diffusion);
        }

        $theseFichiersExpurgesItems = array_map(function (FichierThese $fichier) use ($these) {
            return [
                'file'          => $fichier,
                'downloadUrl'   => $this->urlFichierThese()->telechargerFichierThese($these, $fichier),
            ];
        }, $theseFichiersExpurges);
        $annexesFichiersExpurgesItems = array_map(function (FichierThese $fichier) use ($these) {
            return [
                'file'          => $fichier,
                'downloadUrl'   => $this->urlFichierThese()->telechargerFichierThese($these, $fichier),
            ];
        }, $annexesFichiersExpurges);

        $view = new ViewModel([
            'these'                        => $these,
            'diffusion'                    => $diffusion,
            'version'                      => $version,
            'form'                         => $form,
            'theseFichiersExpurgesItems'   => $theseFichiersExpurgesItems,
            'annexesFichiersExpurgesItems' => $annexesFichiersExpurgesItems,
            'modifierDiffusionUrl'         => $this->urlDepot()->modifierDiffusionUrl($these, $version),
            'exporterConventionMelUrl'     => $this->urlDepot()->exporterConventionMiseEnLigneUrl($these, $version),
            'hasFichierThese'              => $hasFichierThese,
            'resaisirAutorisationDiffusionVersionCorrigee' => $this->depotService->getResaisirAutorisationDiffusionVersionCorrigee(),
        ]);
        $view->setTemplate('depot/depot/diffusion');

        return $view;
    }

    public function modifierDiffusionAction()
    {
        $these = $this->requestedThese();

        // si le fichier de la thèse originale est une version corrigée, la version de diffusion est aussi en version corrigée
        $existeVersionOrigCorrig = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ORIG_CORR));
        $version = $this->fichierTheseService->fetchVersionFichier($existeVersionOrigCorrig ? VersionFichier::CODE_DIFF_CORR : VersionFichier::CODE_DIFF);
        $diffusion = $these->getDiffusionForVersion($version);

        $form = $this->getDiffusionForm($version);

        if ($diffusion === null) {
            $diffusion = new Diffusion();
            $diffusion->setThese($these);
        }

        $form->bind($diffusion);

        if ($this->getRequest()->isPost()) {
            /** @var ParametersInterface $post */
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            $isValid = $form->isValid();
            if ($isValid) {
                /** @var Diffusion $diffusion */
                $diffusion = $form->getData();
                $diffusion->setVersionCorrigee($version->estVersionCorrigee());

                $this->depotService->updateDiffusion($these, $diffusion, $version);

                // suppression des fichiers expurgés éventuellement déposés en l'absence de pb de droit d'auteur
                $besoinVersionExpurgee = ! $diffusion->getDroitAuteurOk();
                $fichierThesesExpurgesDeposes = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, null , $version, false);
                if (! $besoinVersionExpurgee && !empty($fichierThesesExpurgesDeposes)) {
                    $this->fichierTheseService->deleteFichiers($fichierThesesExpurgesDeposes, $these);
//                    $this->flashMessenger()->addSuccessMessage("Les fichiers expurgés fournis devenus inutiles ont été supprimés.");
                }

                if (! $this->getRequest()->isXmlHttpRequest()) {
                    $url = $this->urlDepot()->depotThese($these, $version->getCode());
                    return $this->redirect()->toUrl($url);
                }
            }
        }

        $form->setAttribute('action', $this->urlDepot()->modifierDiffusionUrl($these, $version));

        return new ViewModel([
            'these'      => $these,
            'diffusion'  => $diffusion,
            'form'       => $form,
            'title'      => "Autorisation de diffusion",
            'theseUrl'   => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_THESE_PDF, $version),
            'annexesUrl' => $this->urlDepot()->depotFichiers($these, NatureFichier::CODE_FICHIER_NON_PDF, $version),
        ]);
    }

    /**
     * @param VersionFichier $version
     * @return \Depot\Form\Diffusion\DiffusionTheseForm
     */
    private function getDiffusionForm(VersionFichier $version)
    {
        $these = $this->requestedThese();

        /** @var \Depot\Form\Diffusion\DiffusionTheseForm $form */
        $form = $this->diffusionTheseForm;
        $form->setVersionFichier($version);

        return $form;
    }

    public function exporterConventionMiseEnLigneAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));
        $diffusion = $these->getDiffusionForVersion($version);
        $attestation = $these->getAttestationForVersion($version);

        /** @var \Depot\Form\Diffusion\DiffusionTheseForm $form */
        $form = $this->diffusionTheseForm;

        $dateObs = $these->getDateSoutenance() ?: $these->getDatePrevisionSoutenance();
        $variableRepo = $this->variableService->getRepository();
        $ETB_LIB = $variableRepo->findOneByCodeAndEtab(Variable::CODE_ETB_LIB, $these->getEtablissement(), $dateObs);
        $ETB_ART_ETB_LIB = $variableRepo->findOneByCodeAndEtab(Variable::CODE_ETB_ART_ETB_LIB, $these->getEtablissement(), $dateObs);

        $etab = $ETB_LIB ? $ETB_LIB->getValeur() : "(Variable ETB_LIB introuvable)";
        $l = $ETB_ART_ETB_LIB ? $ETB_ART_ETB_LIB->getValeur() : "";
        $letab = lcfirst($l) . $etab;
        $libEtablissementA = "à " . $letab;
        $libEtablissementLe = $letab;
        $libEtablissementDe = "de " . $letab;

        $etablissement = $this->etablissementService->fetchEtablissementComue();
        if ($etablissement === null) {
            $etablissement = $these->getEtablissement();
        }
        try {
            $cheminLogo = $this->fichierStorageService->getFileForLogoStructure($etablissement->getStructure());
        } catch (StorageAdapterException $e) {
            $cheminLogo = null;
        }

        $renderer = $this->renderer;
        $exporter = new ConventionPdfExporter($renderer, 'A4');
        $exporter->setVars([
            'etablissement'      => $etablissement,
            'logo'               => $cheminLogo,
            'these'              => $these,
            'diffusion'          => $diffusion,
            'attestation'        => $attestation,
            'form'               => $form,
            'libEtablissement'   => $etab,
            'libEtablissementA'  => $libEtablissementA,
            'libEtablissementLe' => $libEtablissementLe,
            'libEtablissementDe' => $libEtablissementDe,
        ]);
        $exporter->export('Convention_MEL.pdf');
        exit;
    }

    public function modifierDescriptionAction()
    {
        $these = $this->requestedThese();

        $form = $this->getDescriptionForm();

        if ($this->getRequest()->isPost()) {
            /** @var ParametersInterface $post */
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                /** @var MetadonneeThese $metadonnee */
                $metadonnee = $form->getData();
                $this->depotService->updateMetadonnees($these, $metadonnee);

                if (! $this->getRequest()->isXmlHttpRequest()) {
                    return $this->redirect()->toRoute('these/description', [], [], true);
                }
            }
        }

        $form->setAttribute('action', $this->urlDepot()->modifierMetadonneesUrl($these));

        return new ViewModel([
            'these' => $these,
            'form' => $form,
            'title' => "Signalement",
        ]);
    }

    /**
     * @return \Depot\Form\Metadonnees\MetadonneeTheseForm
     */
    private function getDescriptionForm()
    {
        $these = $this->requestedThese();

        /** @var MetadonneeTheseForm $form */
        $form = $this->metadonneeTheseForm;

        $description = $these->getMetadonnee();

        if ($description === null) {
            $description = new MetadonneeThese();
            $description->setTitre($these->getTitre());
        }

        $form->bind($description);

        return $form;
    }

    public function modifierCertifConformiteAction()
    {
        $these = $this->requestedThese();
        $versionArchivage = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));

        $fichierTheseRetraite = current($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $versionArchivage, true));

        $form = new ConformiteFichierForm('conformite');

        if ($this->getRequest()->isPost()) {
            /** @var ParametersInterface $post */
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                $conforme = $post->get('conforme');
                $this->fichierTheseService->updateConformiteFichierTheseRetraitee($these, $conforme);
                if ($conforme && $versionArchivage->estVersionCorrigee()) {
                    $this->depotValidationService->validateDepotTheseCorrigee($these);
                    $this->depotService->notifierCorrectionsApportees($these);
                }
            }
        }
        else {
            $conforme = $fichierTheseRetraite->getEstConforme();
            $form->get('conforme')->setValue($conforme !== null ? (string) $conforme : null);
        }

        $form->setAttribute('action', $this->urlDepot()->certifierConformiteTheseRetraiteUrl($these, $versionArchivage));

        return new ViewModel([
            'these' => $these,
            'form' => $form,
            'title' => "Conformité de " . $versionArchivage->toString(),
        ]);
    }

    public function depotPapierFinalAction() {

        $these = $this->requestedThese();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->depotValidationService->validateVersionPapierCorrigee($these);
            $this->flashMessenger()->addSuccessMessage("Validation enregistrée avec succès.");
            return $this->redirect()->toRoute('these/version-papier', [], [], true);
        }

        $validations = $this->validationService->getRepository()->findValidationByCodeAndThese(
            TypeValidation::CODE_VERSION_PAPIER_CORRIGEE,
            $these
        );

        return new ViewModel(array(
            'these' => $these,
            'validation' => empty($validations) ? null : $validations[0],
        ));

    }

    /**
     * Spécifie le timout à appliquer au lancement du script de retraitement.
     *
     * @param string $timeoutRetraitement Ex: '30s', '1m', cf. "man timout".
     * @return self
     */
    public function setTimeoutRetraitement($timeoutRetraitement)
    {
        $this->timeoutRetraitement = $timeoutRetraitement;

        return $this;
    }


    /**
     * @return ViewModel
     * @throws OptimisticLockException
     */
    public function pointsDeVigilanceAction() {

        $these = $this->requestedThese();

        $rdvBu = $this->entityManager->getRepository(RdvBu::class)->findOneBy(["these" => $these]);

        $form = null;
        if ($rdvBu !== null) {
            /** @var PointsDeVigilanceForm $form */
            $form = $this->pointsDeVigilanceForm;
            $form->bind($rdvBu);

            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $form->setData($this->getRequest()->getPost()); // appel de Hydrator::hydrate

                if ($form->isValid()) {
                    $this->entityManager->flush($rdvBu);

                    //message de notification dans la page
                    $message = "Les points de vigilance viennent d'être sauvegardés.";
                    $this->flashMessenger()->addSuccessMessage($message);
                    $this->redirect()->toRoute('these/points-de-vigilance', ["these" => $these->getId()]);
                }
            }
        }

        return new ViewModel([
            'these' => $these,
            'form' => $form,
        ]);
    }

    public function fusionAction()
    {
        /** @var These $these */
        $these          = $this->requestedThese();
        $corrigee       = $this->params()->fromRoute("corrigee");
        $versionName    = $this->params()->fromRoute("version");
        $removal        = (bool) $this->params()->fromRoute("removal");

        $versionFichier = null;
        if ($versionName !== null) {
            switch($versionName) {
                case "VO" : $versionFichier = VersionFichier::CODE_ORIG;
                    break;
                case "VA" : $versionFichier = VersionFichier::CODE_ARCHI;
                    break;
                case "VD" : $versionFichier = VersionFichier::CODE_DIFF;
                    break;
                case "VOC" : $versionFichier = VersionFichier::CODE_ORIG_CORR;
                    break;
                case "VAC" : $versionFichier = VersionFichier::CODE_ARCHI_CORR;
                    break;
                case "VDC" : $versionFichier = VersionFichier::CODE_DIFF_CORR;
                    break;
                default : throw  new RuntimeException("Version [".$versionName."] inconnue.");
            }
        } else {
            //doctorant
            if ($corrigee === null) {
                //tester si il existe une VA
                if ($this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_ARCHI)) {
                    $versionFichier = VersionFichier::CODE_ARCHI;
                } else {
                    $versionFichier = VersionFichier::CODE_ORIG;
                }
            } else {
                //tester si il existe une VAC
                if ($this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_ARCHI_CORR)) {
                    $versionFichier = VersionFichier::CODE_ARCHI_CORR;
                } else {
                    $versionFichier = VersionFichier::CODE_ORIG_CORR;
                }
            }
        }

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);

        try {
            // Un timeout peut être appliqué au lancement du  script.
            // Si ce timout est atteint, l'exécution du script est interrompue
            // et une exception TimedOutCommandException est levée.
            $timeout = $this->timeoutRetraitement;
            $outputFilePath = $this->fichierTheseService->fusionnerPdcEtThese($these, $pdcData, $versionFichier, $removal, $timeout);
        } catch (TimedOutCommandException $toce) {
            $destinataires = [ $this->userContextService->getIdentityDb()->getEmail() ] ;
            // relancer le retraitement en tâche de fond
            $this->fichierTheseService->fusionneFichierTheseAsync($these, $versionFichier, $removal, $destinataires);

            return $this->redirect()->toRoute('these/rdv-bu', ['these' => $these->getId(), 'asynchronous' => 1], [], true);
        }

        /** Retourner un PDF ...  */
        $content = file_get_contents($outputFilePath);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . 'application/pdf');
        header('Content-Disposition: attachment; filename=' . trim(strrchr($outputFilePath, '/'), '/'));
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');

        echo $content;
        exit;
    }
}