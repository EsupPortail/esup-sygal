<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Soutenance\Entity\Justificatif;
use Soutenance\Form\Justificatif\JustificatifFormAwareTrait;
use Soutenance\Provider\Parametre\SoutenanceParametres;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class JustificatifController extends AbstractController
{
    use FichierTheseServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;

    use JustificatifFormAwareTrait;

    public function ajouterAction() : ViewModel
    {
        $proposition = $this->getPropositionService()->getRequestedProposition($this);
        $nature = $this->params()->fromRoute('nature');
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $justificatif = new Justificatif();
        $justificatif->setMembre($membre);
        $justificatif->setProposition($proposition);
        $form = $this->getJustificatifForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/justificatif/ajouter', ['proposition' => $proposition->getId(), 'nature' => $nature, 'membre' => $membre->getId()], [], true));
        $form->bind($justificatif);

        $request = $this->getRequest();
        if ($request->isPost()) {
//            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];

            if (!empty($files)) {
                $nature = $this->fichierTheseService->fetchNatureFichier($nature);
                $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->fichierTheseService->createFichierThesesFromUpload($proposition->getThese(), $files, $nature, $version);
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
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $nature = $this->fichierTheseService->fetchNatureFichier($this->params()->fromRoute('nature'));

        $justificatif = new Justificatif();
        $justificatif->setProposition($proposition);
        $form = $this->getJustificatifForm();
        $form->setMembresAsOptions($this->propositionService->extractMembresAsOptionsFromProposition($proposition));
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/justificatif/ajouter-justificatif', ['these' => $these->getId()], [], true));
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
                if ($nature === null) $nature = $this->fichierTheseService->fetchNatureFichier($data['nature']);
                if ($data['membre']) {
                    $membre = $this->getMembreService()->find($data['membre']);
                    $justificatif->setMembre($membre);
                }
                $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->fichierTheseService->createFichierThesesFromUpload($these, $files, $nature, $version);
                $justificatif->setFichier($fichiers[0]);

                $this->getJustificatifService()->create($justificatif);
                exit();
            }
        }

        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition);

        $vm =  new ViewModel([
            'title' => "Téléversement d'un justificatif",
            'these' => $these,
            'form' => $form,
            'justificatifs' => $justificatifs,

            'FORMULAIRE_DELOCALISATION' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELOCALISATION),
            'FORMULAIRE_DELEGUATION' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELEGATION_SIGNATURE),
            'FORMULAIRE_DEMANDE_LABEL' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_LABEL_EUROPEEN),
            'FORMULAIRE_DEMANDE_ANGLAIS' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_REDACTION_ANGLAIS),
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_CONFIDENTIALITE),
        ]);
        if ($nature !== null) $vm->setTemplate('soutenance/justificatif/ajouter');
        return $vm;
    }

    public function ajouterDocumentLieSoutenanceAction() : ViewModel
    {
        $codeNatureFichier = $this->params()->fromQuery('nature');
        $labelFichier = $this->params()->fromQuery('label');

        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $fichier = new Fichier();
        $nature = $this->fichierTheseService->fetchNatureFichier($codeNatureFichier);
        $fichier->setNature($nature);

        $justificatif = new Justificatif();
        $justificatif->setProposition($proposition);
        $form = $this->getJustificatifForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/justificatif/ajouter-document-lie-soutenance', ['these' => $these->getId()], ["query" => [
            "nature" => $codeNatureFichier,
            "label" => $labelFichier
        ]], true));
        $form->bind($justificatif);
        $form->init();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $files = ['files' => $request->getFiles()->toArray()];

            if (!empty($files)) {
                $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->fichierTheseService->createFichierThesesFromUpload($these, $files, $nature, $version);
                $justificatif->setFichier($fichiers[0]);
                $this->getJustificatifService()->create($justificatif);
            }
            exit();
        }


        $vm =  new ViewModel([
            'title' => "Téléversement du document : ".$labelFichier,
            'these' => $these,
            'form' => $form,
        ]);
        $vm->setTemplate('soutenance/justificatif/ajouter');
        return $vm;
    }
}