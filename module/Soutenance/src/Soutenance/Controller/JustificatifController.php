<?php

namespace Soutenance\Controller;

use Depot\Service\FichierHDR\FichierHDRServiceAwareTrait;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Exception;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\PropositionThese;
use Soutenance\Form\Justificatif\JustificatifFormAwareTrait;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class JustificatifController extends AbstractSoutenanceController
{
    use FichierTheseServiceAwareTrait;
    use FichierHDRServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ParametreServiceAwareTrait;

    use JustificatifFormAwareTrait;

    public function ajouterAction() : ViewModel
    {
        $this->initializeFromType(false, false, false);

        $nature = $this->params()->fromRoute('nature');
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $justificatif = new Justificatif();
        $justificatif->setMembre($membre);
        $justificatif->setProposition($this->proposition);
        $form = $this->getJustificatifForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/justificatif/ajouter", ['proposition' => $this->proposition->getId(), 'nature' => $nature, 'membre' => $membre->getId()], [], true));
        $form->bind($justificatif);

        $request = $this->getRequest();
        if ($request->isPost()) {
//            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];

            if (!empty($files)) {
                $nature = $this->isThese()
                    ? $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_DELEGUATION_SIGNATURE)
                    : $this->fichierHDRService->fetchNatureFichier(NatureFichier::CODE_DELEGUATION_SIGNATURE);
                $version = $this->isThese()
                    ? $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG)
                    : $this->fichierHDRService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->isThese()
                    ? $this->fichierTheseService->createFichierThesesFromUpload($this->entity, $files, $nature, $version)
                    : $this->fichierHDRService->createFichierHDRsFromUpload($this->entity, $files, $nature, $version);
                $justificatif->setFichier($fichiers[0]);
                $this->getJustificatifService()->create($justificatif);
            }
        }

        return new ViewModel([
            'title' => "Ajout d'un justificatif pour " . $membre->getDenomination(),
            'form' => $form,
        ]);
    }

    public function retirerAction() : Response
    {
        $justificatif = $this->getJustificatifService()->getRequestedJustificatif($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getJustificatifService()->historise($justificatif);

        return $this->redirect()->toUrl($retour);
    }

    public function ajouterJustificatifAction() : ViewModel
    {
        $this->initializeFromType(false, false,false);
        $nature = $this->isThese() ?
            $this->fichierTheseService->fetchNatureFichier($this->params()->fromRoute('nature')) :
            $this->fichierHDRService->fetchNatureFichier($this->params()->fromRoute('nature'));

        $justificatif = new Justificatif();
        $justificatif->setProposition($this->proposition);
        $form = $this->getJustificatifForm();
        $form->setMembresAsOptions($this->propositionService->extractMembresAsOptionsFromProposition($this->proposition));
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/justificatif/ajouter-justificatif", ['object' => $this->entity->getId()], [], true));
        $form->bind($justificatif);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];
            if ($nature) $data['nature'] = $nature->getCode();

//            $form->setData($data);
//            if ($form->isValid()) {
            // <!> Attention : L'hydration echoue ...
            if ($data['nature'] !== null AND !empty($files)) {
                if ($nature === null) $nature = $this->isThese() ?
                    $this->fichierTheseService->fetchNatureFichier($data['nature']) :
                    $this->fichierHDRService->fetchNatureFichier($data['nature']);
                if ($data['membre']) {
                    $membre = $this->getMembreService()->find($data['membre']);
                    $justificatif->setMembre($membre);
                }
                $version = $this->isThese()
                    ? $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG)
                    : $this->fichierHDRService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->isThese()
                    ? $this->fichierTheseService->createFichierThesesFromUpload($this->entity, $files, $nature, $version)
                    : $this->fichierHDRService->createFichierHDRsFromUpload($this->entity, $files, $nature, $version);
                $justificatif->setFichier($fichiers[0]);

                $this->getJustificatifService()->create($justificatif);
                exit();
            }
        }

        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($this->proposition);

        if($this->proposition instanceof PropositionThese){
            try {
                $FORMULAIRE_DELOCALISATION = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELOCALISATION);
                $FORMULAIRE_DELEGATION = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELEGATION_SIGNATURE);
                $FORMULAIRE_DEMANDE_LABEL = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_LABEL_EUROPEEN);
                $FORMULAIRE_DEMANDE_ANGLAIS = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_REDACTION_ANGLAIS);
                $FORMULAIRE_DEMANDE_CONFIDENTIALITE = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_CONFIDENTIALITE);
            } catch (Exception $e) {
                throw new RuntimeException("Une erreur est survenue lors de la récupération de paramètre.",0,$e);
            }
        }else{
            $FORMULAIRE_DELOCALISATION = $this->propositionService->findUrlFormulaireFichierByEtabAndNatureFichierCode($this->entity, NatureFichier::CODE_DELOCALISATION_SOUTENANCE_HDR, $this->urlFichierHDR());
            $FORMULAIRE_DELEGATION = $this->propositionService->findUrlFormulaireFichierByEtabAndNatureFichierCode($this->entity, NatureFichier::CODE_DELEGATION_SIGNATURE_HDR, $this->urlFichierHDR());
            $FORMULAIRE_DEMANDE_CONFIDENTIALITE = $this->propositionService->findUrlFormulaireFichierByEtabAndNatureFichierCode($this->entity, NatureFichier::CODE_DEMANDE_CONFIDENT_HDR, $this->urlFichierHDR());
            $FORMULAIRE_DEMANDE_ANGLAIS = $this->propositionService->findUrlFormulaireFichierByEtabAndNatureFichierCode($this->entity, NatureFichier::CODE_LANGUE_ANGLAISE_HDR, $this->urlFichierHDR());
        }
        $vm =  new ViewModel([
            'title' => "Téléversement d'un justificatif",
            'object' => $this->entity,
            'form' => $form,
            'justificatifs' => $justificatifs,

            'FORMULAIRE_DELOCALISATION' => $FORMULAIRE_DELOCALISATION,
            'FORMULAIRE_DELEGATION' => $FORMULAIRE_DELEGATION,
            'FORMULAIRE_DEMANDE_LABEL' => isset($FORMULAIRE_DEMANDE_LABEL) ?: null,
            'FORMULAIRE_DEMANDE_ANGLAIS' => $FORMULAIRE_DEMANDE_ANGLAIS,
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $FORMULAIRE_DEMANDE_CONFIDENTIALITE,
        ]);
        if ($nature !== null) $vm->setTemplate('soutenance/justificatif/ajouter');
        return $vm;
    }

    public function ajouterDocumentLieSoutenanceAction() : ViewModel
    {
        $this->initializeFromType(false, false, false);
        $codeNatureFichier = $this->params()->fromQuery('nature');
        $labelFichier = $this->params()->fromQuery('label');
        
        $fichier = new Fichier();
        $nature = $this->fichierTheseService->fetchNatureFichier($codeNatureFichier);
        $fichier->setNature($nature);

        $justificatif = new Justificatif();
        $justificatif->setProposition($this->proposition);
        $form = $this->getJustificatifForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/justificatif/ajouter-document-lie-soutenance", ['object' => $this->entity->getId()], ["query" => [
            "nature" => $codeNatureFichier,
            "label" => $labelFichier
        ]], true));
        $form->bind($justificatif);
        $form->init();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $files = ['files' => $request->getFiles()->toArray()];

            if (!empty($files)) {
                $version = $this->isThese()
                    ? $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG)
                    : $this->fichierHDRService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->isThese()
                    ? $this->fichierTheseService->createFichierThesesFromUpload($this->entity, $files, $nature, $version)
                    : $this->fichierHDRService->createFichierHDRsFromUpload($this->entity, $files, $nature, $version);
                $justificatif->setFichier($fichiers[0]);
                $this->getJustificatifService()->create($justificatif);
            }
            exit();
        }


        $vm =  new ViewModel([
            'title' => "Téléversement du document : ".$labelFichier,
            'object' => $this->entity,
            'form' => $form,
        ]);
        $vm->setTemplate('soutenance/justificatif/ajouter');
        return $vm;
    }
}