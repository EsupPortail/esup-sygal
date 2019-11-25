<?php

namespace Application\Controller;

use Application\Entity\Db\Attestation;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\FichierThese;
use Application\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Application\Entity\Db\MetadonneeThese;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Variable;
use Application\Entity\Db\VersionFichier;
use Application\Entity\Db\WfEtape;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Form\AttestationTheseForm;
use Application\Form\ConformiteFichierForm;
use Application\Form\DiffusionTheseForm;
use Application\Form\MetadonneeTheseForm;
use Application\Form\PointsDeVigilanceForm;
use Application\Form\RdvBuTheseDoctorantForm;
use Application\Form\RdvBuTheseForm;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\FichierThese\Exception\ValidationImpossibleException;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\MailConfirmationServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\These\Convention\ConventionPdfExporter;
use Application\Service\These\Filter\TheseSelectFilter;
use Application\Service\These\TheseRechercheServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Application\Service\Workflow\WorkflowServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Import\Service\Traits\ImportServiceAwareTrait;
use Retraitement\Exception\TimedOutCommandException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareTrait;
use UnicaenApp\Traits\MessageAwareInterface;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Submit;
use Zend\Form\Form;
use Zend\Http\Response;
use Zend\InputFilter\InputFilter;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

class TheseController extends AbstractController
{
    use FichierTheseServiceAwareTrait;
    use MessageCollectorAwareTrait;
    use NotifierServiceAwareTrait;
    use RoleServiceAwareTrait;
    use TheseRechercheServiceAwareTrait;
    use TheseServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use WorkflowServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use EtablissementServiceAwareTrait;
    use EntityManagerAwareTrait;
    use MailConfirmationServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use ImportServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use VariableServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    private $timeoutRetraitement;

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        /**
         * NB (2019/03/20) : désactiver pour donner l'accès à toutes les thèses pour les rôles doctorant et directeur/co-directeur
         * L'utilisateur est un doctorant, redirection vers l'accueil. *
         */
//        if ($this->userContextService->getSelectedRoleDoctorant()) {
//            return $this->redirect()->toRoute('home');
//        }

        $queryParams = $this->params()->fromQuery();
        $text = $this->params()->fromQuery('text');

        // Application des filtres et tris par défaut, puis redirection éventuelle
        $filtersUpdated = $this->theseRechercheService->updateQueryParamsWithDefaultFilters($queryParams);
        $sortersUpdated = $this->theseRechercheService->updateQueryParamsWithDefaultSorters($queryParams);
        if ($filtersUpdated || $sortersUpdated) {
            return $this->redirect()->toRoute(null, [], ['query' => $queryParams], true);
        }

        $this->theseRechercheService
            ->createFiltersWithUnpopulatedOptions()
            ->createSorters()
            ->processQueryParams($queryParams);

        $etablissement = $this->theseRechercheService->getFilterValueByName(TheseSelectFilter::NAME_etablissement);
        $etatThese     = $this->theseRechercheService->getFilterValueByName(TheseSelectFilter::NAME_etatThese);

        /** Configuration du paginator **/
        $qb = $this->theseRechercheService->createQueryBuilder();
        $maxi = $this->params()->fromQuery('maxi', 50);
        $page = $this->params()->fromQuery('page', 1);
        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(30)
            ->setItemCountPerPage((int)$maxi)
            ->setCurrentPageNumber((int)$page);

        return new ViewModel([
            'theses'                => $paginator,
            'text'                  => $text,
            'roleDirecteurThese'    => $this->roleService->getRepository()->findOneBy(['sourceCode' => Role::CODE_DIRECTEUR_THESE]),
            'displayEtablissement'  => !$etablissement,
            'displayDateSoutenance' => $etatThese === These::ETAT_SOUTENUE || !$etatThese,
            'etatThese'             => $etatThese,
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function filtersAction()
    {
        $queryParams = $this->params()->fromQuery();

        $this->theseRechercheService
            ->createFilters()
            ->processQueryParams($queryParams);

        return new ViewModel([
            'filters' => $this->theseRechercheService->getFilters(),
        ]);
    }

    public function rechercherAction()
    {
        $prg = $this->postRedirectGet();
        if ($prg instanceof \Zend\Http\PhpEnvironment\Response) {
            return $prg;
        }

        $queryParams = $this->params()->fromQuery();

        if (is_array($prg)) {
            if (isset($queryParams['page'])) {
                unset($queryParams['page']);
            }
            $queryParams['text'] = $prg['text'];
        }

        return $this->redirect()->toRoute('these', [], ['query' => $queryParams]);
    }

    public function roadmapAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these' => $these,
        ]);
        $view->setTemplate('application/these/roadmap');

        return $view;
    }

    public function detailIdentiteAction()
    {
        $these = $this->requestedThese();
        $etablissement = $these->getEtablissement();

        $validationsDesCorrectionsEnAttente = null;
        if ($these->getCorrectionAutorisee()) {
            $validationsDesCorrectionsEnAttente = $this->validationService->getValidationsAttenduesPourCorrectionThese($these);
        }

        /**
         * @var Individu $individu
         * @var MailConfirmation $enCours
         * @var MailConfirmation $confirmee
         */
        $individu = $these->getDoctorant()->getIndividu();
        $enCours = $this->mailConfirmationService->getDemandeEnCoursByIndividu($individu);
        $confirmee = $this->mailConfirmationService->getDemandeConfirmeeByIndividu($individu);

        $mailContact = null;
        $etatMailContact = null;

        switch(true) {
            case($enCours !== null) :
                $mailContact = $enCours->getEmail();
                $etatMailContact = MailConfirmation::ENVOYER;
                break;
            case($confirmee !== null) :
                $mailContact = $confirmee->getEmail();
                $etatMailContact = MailConfirmation::CONFIRMER;
                break;
        }

        $unite = $these->getUniteRecherche();
        $rattachements = null;
        if ($unite !== null) $rattachements = $this->getUniteRechercheService()->findEtablissementRattachement($unite);

        //TODO JP remplacer dans modifierPersopassUrl();
        $urlModification = $this->url()->fromRoute('doctorant/modifier-persopass',['back' => 1, 'doctorant' => $these->getDoctorant()->getId()], [], true);

        $view = new ViewModel([
            'these'                     => $these,
            'etablissement'             => $etablissement,
            'estDoctorant'              => (bool)$this->userContextService->getSelectedRoleDoctorant(),
            'modifierPersopassUrl'      => $urlModification,
            'modifierCorrecAutorUrl'    => $this->urlThese()->modifierCorrecAutoriseeForceeUrl($these),
            'nextStepUrl'               => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::PSEUDO_ETAPE_FINALE,
            ]),
            'mailContact'               => $mailContact,
            'etatMailContact'           => $etatMailContact,
            'rattachements'             => $rattachements,
            'validationsDesCorrectionsEnAttente' => $validationsDesCorrectionsEnAttente,
        ]);
        $view->setTemplate('application/these/identite');

        return $view;
    }

    public function detailDescriptionAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these'                  => $these,
            'modifierMetadonneesUrl' => $this->urlThese()->modifierMetadonneesUrl($these),
            'nextStepUrl'            => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::CODE_SIGNALEMENT_THESE,
                WfEtape::PSEUDO_ETAPE_FINALE,
            ]),
        ]);
        $view->setTemplate('application/these/description');

        return $view;
    }

    /**
     * Import forcé d'une thèse et des inf.
     *
     * @return Response
     */
    public function refreshTheseAction()
    {
        $these = $this->requestedThese();

        $this->importService->setLogger((new Logger())->addWriter(new Noop()));
        $this->importService->updateThese($these);

        $redirect = $this->params()->fromQuery('redirect', '/');

        return $this->redirect()->toUrl($redirect);
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
            'refreshTheseUrl'  => $this->urlThese()->refreshTheseUrl($these, $this->urlThese()->validationPageDeCouvertureUrl($these)),
            'validerUrl'       => $this->urlThese()->validerPageDeCouvertureUrl($these),
            'devaliderUrl'     => $this->urlThese()->devaliderPageDeCouvertureUrl($these),
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
            'theseUrl'         => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_THESE_PDF, $codeVersion),
            'annexesUrl'       => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_FICHIER_NON_PDF, $codeVersion),
            'attestationUrl'   => $this->urlThese()->attestationThese($these, $codeVersion),
            'diffusionUrl'     => $this->urlThese()->diffusionThese($these, $codeVersion),
            'nextStepUrl'      => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::CODE_DEPOT_VERSION_ORIGINALE,
                WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE,
                WfEtape::CODE_ATTESTATIONS,
                WfEtape::CODE_AUTORISATION_DIFFUSION_THESE,
                WfEtape::PSEUDO_ETAPE_FINALE,
            ]),
        ]);

        $view->setTemplate('application/these/depot');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function detailDepotDiversAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these'                     => $these,
            'pvSoutenanceUrl'           => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_PV_SOUTENANCE),
            'rapportSoutenanceUrl'      => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_RAPPORT_SOUTENANCE),
            'preRapportSoutenanceUrl'   => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE),
            'demandeConfidentUrl'       => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_DEMANDE_CONFIDENT),
            'prolongConfidentUrl'       => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_PROLONG_CONFIDENT),
            'convMiseEnLigneUrl'        => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_CONV_MISE_EN_LIGNE),
            'avenantConvMiseEnLigneUrl' => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_AVENANT_CONV_MISE_EN_LIGNE),
        ]);
        $view->setTemplate('application/these/depot-divers');

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
            return $this->redirect()->toUrl($this->urlThese()->depotThese($these));
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

        $theseRetraiteeUrl = $this->urlThese()->depotFichiers($these, NatureFichier::CODE_THESE_PDF, $version, true);

        $view = new ViewModel([
            'these'                              => $these,
            'fichierThese'                       => $fichierThese,
            'fichierTheseRetraite'               => $fichierTheseRetraite,
            'theseFichiersItems'                 => $theseFichiersItems,
            'theseRetraiteeUrl'                  => $theseRetraiteeUrl,
            'testArchivabiliteTheseOriginaleUrl' => $this->urlThese()->testArchivabilite($these, $version),
            'archivabiliteTheseRetraiteeUrl'     => $this->urlThese()->archivabiliteThese($these, $version, true),
            'conformiteTheseRetraiteeUrl'        => $this->urlThese()->conformiteTheseRetraitee($these, $version),
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
        $view->setTemplate('application/these/archivage');

        return $view;
    }

    public function detailRdvBuAction()
    {
        $estDoctorant = (bool) $this->userContextService->getSelectedRoleDoctorant();
        $these = $this->requestedThese();
        $asynchronous = $this->params()->fromRoute('asynchronous');

        $versionArchivable = $this->fichierTheseService->getRepository()->getVersionArchivable($these);
        $hasVA = $this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_ARCHI);
        $hasVD = $this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_DIFF);

        $validationsPdc = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $these);
        $pageCouvValidee = !empty($validationsPdc);

        $view = new ViewModel([
            'these'        => $these,
            'estDoctorant' => $estDoctorant,
            'modifierUrl'  => $this->urlThese()->modifierRdvBuUrl($these),
            'validerUrl'   => $this->urlThese()->validerRdvBuUrl($these),
            'devaliderUrl' => $this->urlThese()->devaliderRdvBuUrl($these),
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

        ]);

        $view->setTemplate('application/these/rdv-bu' . ($estDoctorant ? '-doctorant' : null));

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
                $this->theseService->updateCorrectionAutoriseeForcee($these, $forcage);
            }
        }
        else {
            $form->get('forcage')->setValue($these->getCorrectionAutoriseeForcee() ?: '');
        }

        $form->setAttribute('action', $this->urlThese()->modifierCorrecAutoriseeForceeUrl($these));

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
            These::CORRECTION_AUTORISEE_FORCAGE_AUCUNE      => "Forcer à &laquo; <strong>Aucune correction attendue</strong> &raquo;.",
            These::CORRECTION_AUTORISEE_FORCAGE_FACULTATIVE => "Forcer à &laquo; <strong>Corrections facultatives attendues</strong> &raquo;.",
            These::CORRECTION_AUTORISEE_FORCAGE_OBLIGATOIRE => "Forcer à &laquo; <strong>Corrections obligatoires attendues</strong> &raquo;.",
        ];

        if ($isCorrectionAutoriseeFromImport) {
            $correctionAttendueImportee = sprintf("Corrections %s attendues", strtolower($these->getCorrectionAutoriseeToString(true, false)));
            unset($radioOptions[$valeurCorrectionAutoriseeFromImport]);
        } else {
            $correctionAttendueImportee = "Aucune correction attendue";
            unset($radioOptions[These::CORRECTION_AUTORISEE_FORCAGE_AUCUNE]);
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

    public function modifierRdvBuAction()
    {
        $these = $this->requestedThese();
        $estDoctorant = (bool) $this->userContextService->getSelectedRoleDoctorant();

        $validationsPdc = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $these);
        $pageCouvValidee = !empty($validationsPdc);

        $rdvBu = $these->getRdvBu() ?: new RdvBu($these);
        $rdvBu->setVersionArchivableFournie($this->fichierTheseService->getRepository()->existeVersionArchivable($these));

        /** @var RdvBuTheseForm|RdvBuTheseDoctorantForm $form */
        $form = $this->getServiceLocator()->get('formElementManager')->get($estDoctorant ? 'RdvBuTheseDoctorantForm' : 'RdvBuTheseForm');
        $form->bind($rdvBu);

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                /** @var RdvBu $formRdvBu */
                $formRdvBu = $form->getData();
                $inserting = $formRdvBu->getId() === null;
                $this->theseService->updateRdvBu($these, $formRdvBu);

                $this->flashMessenger()->addMessage("Informations enregistrées avec succès.", 'success');
                if ($message = $this->theseService->getMessage('<br>', MessageAwareInterface::SUCCESS)) {
                    $this->flashMessenger()->addMessage($message, 'rdv_bu/success');
                }
                if ($message = $this->theseService->getMessage('<br>', MessageAwareInterface::INFO)) {
                    $this->flashMessenger()->addMessage($message, 'rdv_bu/info');
                }

                // notification par mail à la BU quand le doctorant saisit les infos pour la 1ere fois
                if ($estDoctorant && $inserting) {
                    $notif = $this->notifierService->getNotificationFactory()->createNotificationForRdvBuSaisiParDoctorant($these, $inserting);
                    $this->notifierService->trigger($notif);
                    $this->notifierService->feedFlashMessenger($this->flashMessenger(), 'rdv_bu/');
                }

                if (! $this->getRequest()->isXmlHttpRequest()) {
                    return $this->redirect()->toRoute('these/rdv-bu', [], [], true);
                }
            }
        }

        $form->setAttribute('action', $this->urlThese()->modifierRdvBuUrl($these));

        $vm = new ViewModel([
            'these' => $these,
            'form'  => $form,
            'title' => "Rendez-vous BU",
            'pageCouvValidee' => $pageCouvValidee,
        ]);

        $vm->setTemplate('application/these/modifier-rdv-bu' . ($estDoctorant ? '-doctorant' : null));

        return $vm;
    }

    public function validationTheseCorrigeeAction()
    {
        $these = $this->requestedThese();

        $hasVAC = $this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_ARCHI_CORR);
        $hasVDC = $this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_DIFF_CORR);

        $view = new ViewModel([
            'these'                           => $these,
            'validationDepotTheseCorrigeeUrl' => $this->urlThese()->validationDepotTheseCorrigeeUrl($these),
            'validationCorrectionTheseUrl'    => $this->urlThese()->validationCorrectionTheseUrl($these),
            'nextStepUrl'                     => $this->urlWorkflow()->nextStepBox($these, null, [
                WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT,
                WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR,
            ], [
                'message' => "Il ne reste plus qu'à fournir à la BU un exemplaire imprimé de la version corrigée pour valider le dépôt.",
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
            'testerFichierUrl' => $this->urlThese()->modifierMetadonneesUrl($these),
        ]);
        $view->setTemplate('application/these/archivage');

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
        $view->setTemplate('application/these/depot/these');

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
                } catch (RuntimeException $re) {
                    // erreur prévue
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
            'creerVersionRetraiteeUrl'  => $this->urlThese()->creerVersionRetraitee($these, $versionArchivage),
            'fichierVOouVOC'            => $fichierVersionOriginale,
            'fichierVAouVAC'            => $fichierVersionArchivage,
            'nature'                    => $nature,
            'versionFichier'            => $versionArchivage,
        ]);
        $view->setTemplate('application/these/archivage/these-retraitee');

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
        $view->setTemplate('application/these/depot/annexes');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotPvSoutenanceAction()
    {
        $these = $this->requestedThese();
        $dateSoutenanceDepassee = $these->getDateSoutenance() && $these->getDateSoutenance() < new \DateTime();

        $view = $this->createViewForFichierAction(NatureFichier::CODE_PV_SOUTENANCE);
        $view->setVariable('isVisible', $dateSoutenanceDepassee);
        $view->setTemplate('application/these/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotRapportSoutenanceAction()
    {
        $these = $this->requestedThese();
        $dateSoutenanceDepassee = $these->getDateSoutenance() && $these->getDateSoutenance() < new \DateTime();

        $view = $this->createViewForFichierAction(NatureFichier::CODE_RAPPORT_SOUTENANCE);
        $view->setVariable('isVisible', $dateSoutenanceDepassee);
        $view->setTemplate('application/these/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotPreRapportSoutenanceAction()
    {
        $these = $this->requestedThese();
        $dateSoutenanceDepassee = $these->getDateSoutenance() && $these->getDateSoutenance() < new \DateTime();

        $view = $this->createViewForFichierAction(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
        $view->setVariable('isVisible', $dateSoutenanceDepassee);
        $view->setVariable('maxUploadableFilesCount', 3);
        $view->setTemplate('application/these/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotDemandeConfidentAction()
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_DEMANDE_CONFIDENT);
        $view->setTemplate('application/these/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotProlongConfidentAction()
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_PROLONG_CONFIDENT);
        $view->setTemplate('application/these/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotConvMiseEnLigneAction()
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_CONV_MISE_EN_LIGNE);
        $view->setTemplate('application/these/depot/fichier-divers');

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function depotAvenantConvMiseEnLigneAction()
    {
        $view = $this->createViewForFichierAction(NatureFichier::CODE_AVENANT_CONV_MISE_EN_LIGNE);
        $view->setTemplate('application/these/depot/fichier-divers');

        return $view;
    }

    /**
     * @param string $codeNatureFichier
     * @return ViewModel
     */
    private function createViewForFichierAction($codeNatureFichier)
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
                        $this->validationService->validateDepotTheseCorrigee($these);

                        // envoi de mail aux directeurs de thèse
                        $this->notifierService->triggerValidationDepotTheseCorrigee($these);
                    }
                } catch (ValidationImpossibleException $vie) {
                    // Le test d'archivabilité du fichier '%s' a rencontré un problème indépendant de notre volonté
                }
            }

            return $this->redirect()->toUrl($this->urlThese()->archivageThese($these, $version->getCode()));
        }

        $view = new ViewModel([
            'these'                => $these,
            'fichierThese'         => $fichierThese,
            'testArchivabiliteUrl' => $this->urlThese()->testArchivabilite($these, $version->getCode()),
        ]);
        $view->setTemplate('application/these/archivage/test-archivabilite');

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

        $variableEmailAssist = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_ASSISTANCE, $these);

        $view = new ViewModel([
            'these'    => $these,
            'fichier'  => $fichierTheseRetraite,
            'retraite' => $retraite,
            'contact'  => $variableEmailAssist->getValeur(),
        ]);
        $view->setTemplate('application/these/archivage/archivabilite-these');

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

        $variableEmailAssist = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_ASSISTANCE, $these);

        $view = new ViewModel([
            'these'                     => $these,
            'fichierTheseRetraite'      => $fichier,
            'validerFichierRetraiteUrl' => $this->urlThese()->certifierConformiteTheseRetraiteUrl($these, $versionArchivage),
            'contact'                   => $variableEmailAssist->getValeur(),
        ]);
        $view->setTemplate('application/these/archivage/conformite-these-retraitee');

        return $view;
    }

    public function attestationAction()
    {
        $these = $this->requestedThese();
        $attestation = $these->getAttestation();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));
        $hasFichierThese = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, false));

// Est-ce vraiment indispensable d'interroger le moteur du WF ?
// Mis en commentaire pour accélerer l'affichage...
//        $versionInitialeAtteignable = $this->workflowService->findOneByEtape($these, WfEtape::CODE_ATTESTATIONS)->getAtteignable();
//        $versionCorrigeeAtteignable = $this->workflowService->findOneByEtape($these, WfEtape::CODE_ATTESTATIONS_VERSION_CORRIGEE)->getAtteignable();
//        $visible =
//            $version->estVersionCorrigee() && $versionCorrigeeAtteignable ||
//            !$version->estVersionCorrigee() && $versionInitialeAtteignable && !$versionCorrigeeAtteignable;
//
//        if (! $visible) {
//            return false;
//        }
        if (! $hasFichierThese) {
            return false;
        }

        $form = $this->getAttestationTheseForm();

        $view = new ViewModel([
            'these'                  => $these,
            'version'                => $version,
            'attestation'            => $attestation,
            'form'                   => $form,
            'modifierAttestationUrl' => $this->urlThese()->modifierAttestationUrl($these),
            'hasFichierThese'        => $hasFichierThese,
        ]);
        $view->setTemplate('application/these/attestation');

        return $view;
    }

    public function modifierAttestationAction()
    {
        $these = $this->requestedThese();
        $form = $this->getAttestationTheseForm();

        if ($this->getRequest()->isPost()) {
            /** @var ParametersInterface $post */
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            $isValid = $form->isValid();
            if ($isValid) {
                /** @var Attestation $attestation */
                $attestation = $form->getData();
                $this->theseService->updateAttestation($these, $attestation);

                if (! $this->getRequest()->isXmlHttpRequest()) {
                    return $this->redirect()->toRoute('these/depot', [], [], true);
                }
            }
        }

        $form->setAttribute('action', $this->urlThese()->modifierAttestationUrl($these));

        return new ViewModel([
            'these' => $these,
            'form'  => $form,
            'title' => "Attestations",
        ]);
    }

    /**
     * @return AttestationTheseForm
     */
    private function getAttestationTheseForm()
    {
        $these = $this->requestedThese();

        /** @var AttestationTheseForm $form */
        $form = $this->getServiceLocator()->get('formElementManager')->get('AttestationTheseForm');

        $attestation = $these->getAttestation();

        if ($attestation === null) {
            // si l'on est dans le cadre du dépôt de la version corrigée, on rappelle les infos historisées
            if ($this->existeVersionCorrigee()) {
                $attestations = $these->getAttestations($historise = true);
                $attestationPrec = $attestations->last() ?: null; // la plus récente

                /** @var Attestation $attestation */
                $attestation = clone $attestationPrec;
            } else {
                $attestation = new Attestation();
                $attestation->setThese($these);
            }
        }

        $form->bind($attestation);

        return $form;
    }

    /**
     * @var bool
     */
    protected $existeVersionCorrigee = null;

    /**
     * Si le fichier de la thèse originale est une version corrigée, on est dans le cadre d'un dépôt d'une version
     * corrigée et cette fonction retourne true.
     *
     * @param These|null $these
     * @return bool
     */
    private function existeVersionCorrigee(These $these = null)
    {
        if ($these !== null) {
            return (!empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ORIG_CORR)));
        }
        if ($this->existeVersionCorrigee !== null) {
            return $this->existeVersionCorrigee;
        }
        if ($these === null) {
            $these = $this->requestedThese();
        }

        $this->existeVersionCorrigee = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ORIG_CORR));

        return $this->existeVersionCorrigee;
    }

    public function diffusionAction()
    {
        $these = $this->requestedThese();
        $version = $this->fichierTheseService->fetchVersionFichier($this->params()->fromQuery('version'));
        $hasFichierThese = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, false));

// Est-ce vraiment indispensable d'interroger le moteur du WF ?
// Mis en commentaire pour accélerer l'affichage...
//        $versionInitialeAtteignable = $this->workflowService->findOneByEtape($these, WfEtape::CODE_AUTORISATION_DIFFUSION_THESE)->getAtteignable();
//        $versionCorrigeeAtteignable = $this->workflowService->findOneByEtape($these, WfEtape::CODE_AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE)->getAtteignable();
//        $visible =
//            $version->estVersionCorrigee() && $versionCorrigeeAtteignable ||
//            !$version->estVersionCorrigee() && $versionInitialeAtteignable && !$versionCorrigeeAtteignable;
//
//        if (! $visible) {
//            return false;
//        }
        if (! $hasFichierThese) {
            return false;
        }

        /** @var DiffusionTheseForm $form */
        $form = $this->getServiceLocator()->get('formElementManager')->get('DiffusionTheseForm');

        $versionExpurgee = $version->estVersionCorrigee() ? VersionFichier::CODE_DIFF_CORR : VersionFichier::CODE_DIFF;
        $theseFichiersExpurges = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $versionExpurgee, false);
        $annexesFichiersExpurges = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_FICHIER_NON_PDF, $versionExpurgee, false);

        if ($diffusion = $these->getDiffusion()) {
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
            'version'                      => $version,
            'form'                         => $form,
            'theseFichiersExpurgesItems'   => $theseFichiersExpurgesItems,
            'annexesFichiersExpurgesItems' => $annexesFichiersExpurgesItems,
            'modifierDiffusionUrl'         => $this->urlThese()->modifierDiffusionUrl($these),
            'exporterConventionMelUrl'     => $this->urlThese()->exporterConventionMiseEnLigneUrl($these),
            'hasFichierThese'              => $hasFichierThese,
        ]);
        $view->setTemplate('application/these/diffusion');

        return $view;
    }

    public function modifierDiffusionAction()
    {
        $these = $this->requestedThese();

        // si le fichier de la thèse originale est une version corrigée, la version de diffusion est aussi en version corrigée
        $existeVersionOrigCorrig = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ORIG_CORR));
        $version = $existeVersionOrigCorrig ? VersionFichier::CODE_DIFF_CORR : VersionFichier::CODE_DIFF;

        $form = $this->getDiffusionForm($version);

        if ($this->getRequest()->isPost()) {
            /** @var ParametersInterface $post */
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            $isValid = $form->isValid();
            if ($isValid) {
                /** @var Diffusion $diffusion */
                $diffusion = $form->getData();
                $this->theseService->updateDiffusion($these, $diffusion);

                // suppression des fichiers expurgés éventuellement déposés en l'absence de pb de droit d'auteur
                $besoinVersionExpurgee = ! $diffusion->getDroitAuteurOk();
                $fichierThesesExpurgesDeposes = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, null , $version, false);
                if (! $besoinVersionExpurgee && !empty($fichierThesesExpurgesDeposes)) {
                    $this->fichierTheseService->deleteFichiers($fichierThesesExpurgesDeposes, $these);
//                    $this->flashMessenger()->addSuccessMessage("Les fichiers expurgés fournis devenus inutiles ont été supprimés.");
                }

                if (! $this->getRequest()->isXmlHttpRequest()) {
                    $url = $this->urlThese()->depotThese($these, $version);
                    return $this->redirect()->toUrl($url);
                }
            }
        }

        $form->setAttribute('action', $this->urlThese()->modifierDiffusionUrl($these));

        return new ViewModel([
            'these'      => $these,
            'form'       => $form,
            'title'      => "Autorisation de diffusion",
            'theseUrl'   => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_THESE_PDF, $version),
//            'annexesUrl' => $this->urlThese()->fichiersAnnexes($these, NatureFichier::CODE_FICHIER_NON_PDF, $version),
            'annexesUrl' => $this->urlThese()->depotFichiers($these, NatureFichier::CODE_FICHIER_NON_PDF, $version),
        ]);
    }

    /**
     * @param string $version
     * @return DiffusionTheseForm
     */
    private function getDiffusionForm($version)
    {
        $these = $this->requestedThese();

        /** @var DiffusionTheseForm $form */
        $form = $this->getServiceLocator()->get('formElementManager')->get('DiffusionTheseForm');
        $form->setVersionFichier($this->versionFichierService->getRepository()->findOneByCode($version));

        $diffusion = $these->getDiffusion();

        if ($diffusion === null) {
            // si l'on est dans le cadre du dépôt de la version corrigée, on rappelle les infos historisées
            if ($this->existeVersionCorrigee()) {
                $diffusions = $these->getDiffusions($historise = true);
                $diffusionPrec = $diffusions->last() ?: null; // la plus récente

                /** @var Diffusion $diffusion */
                $diffusion = clone $diffusionPrec;
            } else {
                $diffusion = new Diffusion();
                $diffusion->setThese($these);
            }
        }

        $form->bind($diffusion);

        return $form;
    }

    public function exporterConventionMiseEnLigneAction()
    {
        $these = $this->requestedThese();

        /** @var DiffusionTheseForm $form */
        $form = $this->getServiceLocator()->get('formElementManager')->get('DiffusionTheseForm');

        $codes = [
            Variable::CODE_ETB_LIB,
            Variable::CODE_ETB_ART_ETB_LIB,
            Variable::CODE_TRIBUNAL_COMPETENT,
        ];
        $dateObs = $these->getDateSoutenance() ?: $these->getDatePrevisionSoutenance();
        $variableRepo = $this->variableService->getRepository();
        $vars = $variableRepo->findByCodeAndEtab($codes, $these->getEtablissement(), $dateObs);
        $etab = $vars[Variable::CODE_ETB_LIB]->getValeur();
        $letab = lcfirst($vars[Variable::CODE_ETB_ART_ETB_LIB]->getValeur()) . $etab;
        $libEtablissementA = "à " . $letab;
        $libEtablissementLe = $letab;
        $libEtablissementDe = "de " . $letab;
        $libTribunal = lcfirst($vars[Variable::CODE_TRIBUNAL_COMPETENT]->getValeur());

        $renderer = $this->getServiceLocator()->get('view_renderer'); /* @var $renderer \Zend\View\Renderer\PhpRenderer */
        $exporter = new ConventionPdfExporter($renderer, 'A4');
        $exporter->setVars([
            'these'              => $these,
            'form'               => $form,
            'libEtablissement'   => $etab,
            'libEtablissementA'  => $libEtablissementA,
            'libEtablissementLe' => $libEtablissementLe,
            'libEtablissementDe' => $libEtablissementDe,
            'libTribunal'        => $libTribunal,
        ]);
        $exporter->export('export.pdf');
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
                $this->theseService->updateMetadonnees($these, $metadonnee);

                if (! $this->getRequest()->isXmlHttpRequest()) {
                    return $this->redirect()->toRoute('these/description', [], [], true);
                }
            }
        }

        $form->setAttribute('action', $this->urlThese()->modifierMetadonneesUrl($these));

        return new ViewModel([
            'these' => $these,
            'form' => $form,
            'title' => "Signalement",
        ]);
    }

    /**
     * @return MetadonneeTheseForm
     */
    private function getDescriptionForm()
    {
        $these = $this->requestedThese();

        /** @var MetadonneeTheseForm $form */
        $form = $this->getServiceLocator()->get('formElementManager')->get('MetadonneeTheseForm');

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
                    $this->validationService->validateDepotTheseCorrigee($these);

                    // notification des directeurs de thèse
                    $this->notifierService->triggerValidationDepotTheseCorrigee($these);
                }
            }
        }
        else {
            $conforme = $fichierTheseRetraite->getEstConforme();
            $form->get('conforme')->setValue($conforme !== null ? (string) $conforme : null);
        }

        $form->setAttribute('action', $this->urlThese()->certifierConformiteTheseRetraiteUrl($these, $versionArchivage));

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
            $this->validationService->validateVersionPapierCorrigee($these);
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
            $form = $this->getServiceLocator()->get('formElementManager')->get('PointsDeVigilanceForm');
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
        $removal        = $this->params()->fromRoute("removal");

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
            // Un timeout peut être appliqué au lancement du  script de retraitement.
            // Si ce timout est atteint, l'exécution du script est interrompue
            // et une exception TimedOutCommandException est levée.
            $timeout = $this->timeoutRetraitement;
            $outputFilePath = $this->fichierTheseService->fusionnerPdcEtThese($these, $pdcData, $versionFichier, $removal, $timeout);
        } catch (TimedOutCommandException $toce) {
            $destinataires = [ $this->userContextService->getIdentityDb()->getEmail() ] ;
            // relancer le retraitement en tâche de fond
            $this->fichierTheseService->fusionneFichierTheseAsync($these, $versionFichier, $removal, $destinataires);
            return $this->redirect()->toRoute('these/rdv-bu', ['these' => $these->getId(), 'asynchronous' => 1], [], true);
            exit();
        } catch (RuntimeException $re) {
            // erreur prévue
        }

        /** Retourner un PDF ...  */
        $contenu     = file_get_contents($outputFilePath);
        $content     = is_resource($contenu) ? stream_get_contents($contenu) : $contenu;

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