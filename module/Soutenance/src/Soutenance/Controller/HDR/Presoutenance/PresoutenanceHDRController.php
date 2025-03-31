<?php

namespace Soutenance\Controller\HDR\Presoutenance;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Fieldset\ActeurHDR\ActeurHDRFieldset;
use Acteur\Form\ActeurHDR\ActeurHDRFormAwareTrait;
use Application\Entity\Db\Role;
use Exception;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Entity\Membre;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Provider\Parametre\HDR\SoutenanceParametres;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Exporter\Convocation\ConvocationPdfExporter;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use UnicaenApp\Exception\RuntimeException;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;

/** @method FlashMessenger flashMessenger() */
/**
 * @property PropositionHDRService $propositionService
 * @property HDR $entity
 * @property PropositionHDR $proposition
 */
class PresoutenanceHDRController extends PresoutenanceController
{
    use ParametreServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use FichierServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use FichierStorageServiceAwareTrait;

    use ActeurHDRFormAwareTrait;

    /** @var PhpRenderer */
    protected PhpRenderer $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer(PhpRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function presoutenanceAction() : ViewModel
    {
        $this->initializeFromType();

        /** @var Membre[] $membres */
        $membres = $this->proposition->getMembres()->toArray();
        $acteursMembres = $this->acteurService->getRepository()->findActeursForSoutenanceMembres($membres);

        $rapporteurs = $this->propositionService->getRapporteurs($this->proposition);
        $acteursRapporteurs = $this->acteurService->getRepository()->findActeursForSoutenanceMembres($rapporteurs);
        $nbRapporteurs = count($rapporteurs);

        $renduRapport = $this->proposition->getRenduRapport();
        if (!$renduRapport) $this->propositionService->initialisationDateRetour($this->proposition);

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->justificatifService->generateListeJustificatif($this->proposition);
        $justificatifsOk = $this->justificatifService->isJustificatifsOk($this->proposition, $justificatifs);

        $documentsLiesSoutenance = $this->justificatifService->generateListeDocumentsLiesSoutenance($this->proposition);

        /** ==> clef: Membre->getActeur()->getIndividu()->getId() <== */
        $engagements = $this->engagementImpartialiteService->getEngagmentsImpartialiteByEntity($this->entity, $rapporteurs);
        $avis = $this->avisService->getAvisByProposition($this->proposition);

        $validationBDD = $this->validationService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);

        /** Parametres ---------------------------------------------------------------------------------------------- */
        try {
            $deadline = $this->parametreService->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DELAI_RETOUR);
        } catch (Exception $e) {
            throw new RuntimeException("Une erreur est survenue lors de la récupération de la valeur d'un paramètre", 0 , $e);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/presoutenance-hdr/presoutenance');
        $vm->setVariables([
            'hdr' => $this->entity,
            'typeProposition' => $this->type,
            'proposition' => $this->proposition,
            'rapporteurs' => $rapporteurs,
            'acteursRapporteurs' => $acteursRapporteurs,
            'membres' => $membres,
            'acteursMembres' => $acteursMembres,
            'acteursPouvantEtrePresidentJury' => $this->acteurService->getRepository()->findAllActeursPouvantEtrePresidentDuJury($this->proposition),
            'engagements' => $engagements,
            'adresse' => $this->proposition->getAdresseActive(),
            'avis' => $avis,
            'tousLesEngagements' => $nbRapporteurs > 0 && count($engagements) === $nbRapporteurs,
            'tousLesAvis' => $nbRapporteurs > 0 && count($avis) === $nbRapporteurs,
            'urlFichierHDR' => $this->urlFichierHDR(),
            'validationBDD' => $validationBDD,
            'justificatifsOk' => $justificatifsOk,
            'justificatifs' => $justificatifs,

            'deadline' => $deadline,

            'documentsLiesSoutenance' => $documentsLiesSoutenance,
        ]);
        return $vm;
    }

    /**
     * Ici on affecte au membre des individus enregistrés dans SyGAL
     */
    public function associerJurySygalAction() : ViewModel|Response
    {
        $this->initializeFromType();
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $codeRole = "";
        switch ($membre->getRole()) {
            case Membre::RAPPORTEUR_JURY :
            case Membre::RAPPORTEUR_VISIO :
            case Membre::RAPPORTEUR_ABSENT :
                $codeRole = Role::CODE_RAPPORTEUR_JURY;
                break;
            case Membre::MEMBRE_JURY :
                $codeRole = Role::CODE_MEMBRE_JURY;
                break;
        }
        $role = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete($codeRole, $this->entity->getEtablissement());

        $individu = $this->params()->fromQuery('individu') ?  $this->individuService->getRepository()->find($this->params()->fromQuery('individu')) : new Individu();
        $acteur = $this->acteurService->newActeurHDR($this->entity, $individu, $role);
        $acteur->setQualite($membre->getQualite());
        $acteur->setHDR($this->entity);

        $form = $this->acteurHDRForm;
        $form->bind($acteur);
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/presoutenance/associer-jury-sygal", ['hdr' => $this->entity->getId(), 'membre' => $membre->getId()], [], true));

        $roles = $this->applicationRoleService->getRepository()->findAll();
        /** @var ActeurHDRFieldset $acteurFieldset */
        $acteurFieldset = $form->get('acteur');
        $acteurFieldset->setRoles($roles);

        $viewModel = new ViewModel([
            'title' => "Association de " . $membre->getDenomination() . " à un individu " . $this->appInfos()->getNom(),
            'form' => $form,
            'membre' => $membre,
            'hdr' => $this->entity,
            'returnUrl' => $this->url()->fromRoute("soutenance_{$this->type}/presoutenance/associer-jury-sygal", ['hdr' => $this->entity->getId(), 'membre' => $membre->getId()], ["query" => ["modal" => "1"]], true)
        ]);
        $viewModel->setTemplate('acteur/acteur/modifier');

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = $request->getPost();
        $form->setData($data);
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var ActeurHDR $acteur **/
        $acteur = $form->getData();
        try {
            //mise à jour du membre de soutenance
//        $membre->setActeur($acteur);
//        $this->getMembreService()->update($membre);
            $acteur->setMembre($membre);
            $this->acteurService->save($acteur);
        }catch(RuntimeException) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }
        $this->flashMessenger()->addSuccessMessage($membre->getDenomination()." a bien été associé à un acteur.");
        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

        //creation de l'utilisateur
        if ($membre->estRapporteur()) {
            $this->createUtilisateurRapporteur($acteur, $membre);

            //quand c'est une HDR saisie dans SyGAL, on enregistre également un acteur avec le rôle Membre (ce qui était automatique quand cela provenait d'un SI)
            //cet acteur sera supprimé en même temps que l'acteur comportant le rôle Rapporteur, si une dissociation est faite
            if($acteur->getRole()->getCode() !== Role::CODE_RAPPORTEUR_ABSENT){
                $role = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete(Role::CODE_MEMBRE_JURY, $this->entity->getEtablissement());
                $acteurRoleMembre = $this->acteurService->newActeurHDR($this->entity, $acteur->getIndividu(), $role);
                $acteurRoleMembre->setQualite($acteur->getQualite());
                $acteurRoleMembre->setEtablissement($acteur->getEtablissement());
                $acteurRoleMembre->setUniteRecherche($acteur->getUniteRecherche());
                $this->acteurService->save($acteurRoleMembre);
            }
        }
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['hdr' => $this->entity->getId()], [], true);
    }

    /**
     *
     * Génération des documents liés à la présoutenance
     *
     */

    #[NoReturn] public function convocationCandidatAction(): void
    {
        $this->initializeFromType();
        $signature = $this->findSignatureEcoleDoctorale($this->entity) ?: $this->findSignatureEtablissement($this->entity);

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);

        $validationMDD = $this->validationService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'hdr' => $this->entity,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
        ]);
        $exporter->exportDoctorant($this->entity->getId() . '_convocation.pdf');
        exit;
    }
}