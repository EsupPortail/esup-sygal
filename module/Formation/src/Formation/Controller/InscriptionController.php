<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Formation\Entity\Db\Inscription;
use Formation\Provider\NatureFichier\NatureFichier;
use Formation\Service\Exporter\Attestation\AttestationExporter;
use Formation\Service\Exporter\Convocation\ConvocationExporter;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Formation\Service\Notification\NotificationServiceAwareTrait;
use Formation\Service\Presence\PresenceServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InscriptionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use DoctorantServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use NotificationServiceAwareTrait;
    use PresenceServiceAwareTrait;
    use SessionServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

    private ?PhpRenderer $renderer = null;
    public function setRenderer(?PhpRenderer $renderer) { $this->renderer = $renderer; }

    /** CRUD **********************************************************************************************************/

    public function indexAction() : ViewModel
    {
        $filtres = [
            'session' => $this->params()->fromQuery('session'),
            'doctorant' => $this->params()->fromQuery('doctorant'),
            'liste' => $this->params()->fromQuery('liste'),
        ];
        $listings = [
        ];
        /** @var Inscription[] $inscriptions */
        $inscriptions = $this->getInscriptionService()->getRepository()->fetchInscriptionsWithFiltres($filtres);

        return new ViewModel([
            'inscriptions' => $inscriptions,
            'filtres' => $filtres,
            'listings' => $listings,
        ]);
    }

    public function ajouterAction()
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        /** @var Doctorant|null $doctorant */
        $doctorantId = $this->params()->fromRoute('doctorant');
        if ($doctorantId !== null) {
            $doctorant = $this->getEntityManager()->getRepository(Doctorant::class)->find($doctorantId);
        } else {
            $doctorant = null;
        }

        if ($doctorant !== null) {
            $inscription = new Inscription();
            $inscription->setSession($session);
            $inscription->setDoctorant($doctorant);
            $this->getInscriptionService()->create($inscription);
            $this->flashMessenger()->addSuccessMessage("Inscription à la formation [] faite.");

            $this->getNotificationService()->triggerInscriptionEnregistree($inscription);

            $retour=$this->params()->fromQuery('retour');
            if ($retour) return $this->redirect()->toUrl($retour);
            return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            //todo ajouter une fonction de recherche des doctorants directement ...
            if ($data["individu"]["id"] !== null) {
                /** @var Individu $individu */
                $individu = $this->getIndividuService()->getRepository()->find($data["individu"]["id"]);
                $doctorant = $this->doctorantService->getRepository()->findOneByIndividu($individu);
            }
            if ($doctorant !== null) {
                $inscription = new Inscription();
                $inscription->setSession($session);
                $inscription->setDoctorant($doctorant);
                $this->getInscriptionService()->create($inscription);
            }
        }

        return new ViewModel([
            'title' => "Ajout d'une inscription doctorant",
            'session' => $session,
        ]);
    }

    public function historiserAction() : Response
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);
        $this->getInscriptionService()->historise($inscription);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function restaurerAction() : Response
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);
        $this->getInscriptionService()->restore($inscription);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function supprimerAction() : ViewModel
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getInscriptionService()->delete($inscription);
            exit();
        }

        $vm = new ViewModel();
        if ($inscription !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de l'inscription de " . $inscription->getDoctorant()->getIndividu()->getNomComplet(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/inscription/supprimer', ["inscription" => $inscription->getId()], [], true),
            ]);
        }
        return $vm;
    }

    /** GESTION DES LISTES ********************************************************************************************/

    public function passerListePrincipaleAction() : Response
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);

        $session = $inscription->getSession();
        $listePrincipale = $session->getListePrincipale();
        if (count($listePrincipale) < $session->getTailleListePrincipale()) {
            $inscription->setListe(Inscription::LISTE_PRINCIPALE);
            $this->getInscriptionService()->update($inscription);
            $this->getNotificationService()->triggerInscriptionListePrincipale($inscription);
        } else {
            $this->flashMessenger()->addErrorMessage('La liste principale est déjà complète.');
        }

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function passerListeComplementaireAction() : Response
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);

        $session = $inscription->getSession();
        $listePrincipale = $session->getListeComplementaire();
        if (count($listePrincipale) < $session->getTailleListeComplementaire()) {
            $inscription->setListe(Inscription::LISTE_COMPLEMENTAIRE);
            $this->getInscriptionService()->update($inscription);
            $this->getNotificationService()->triggerInscriptionListeComplementaire($inscription);
        } else {
            $this->flashMessenger()->addErrorMessage('La liste complémentaire est déjà complète.');
        }

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function retirerListeAction() : Response
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);
        $inscription->setListe(null);
        $this->getInscriptionService()->update($inscription);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    /** DOCUMENTS LIES AUX INSCRIPTIONS *******************************************************************************/

    public function genererConvocationAction()
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);
        $session = $inscription->getSession();

        $logos = [];
        try {
            $logos['site'] = $this->fichierStorageService->getFileForLogoStructure($session->getSite());
        } catch (StorageAdapterException $e) {
            $logos['site'] = null;
        }
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $logos['comue'] = $this->fichierStorageService->getFileForLogoStructure($comue);
            } catch (StorageAdapterException $e) {
                $logos['comue'] = null;
            }
        }

        $signature = $this->findSignatureEtablissement($inscription->getDoctorant()->getEtablissement());

        //exporter
        $export = new ConvocationExporter($this->renderer, 'A4');
        $export->setVars([
            'signature' => $signature,
            'inscription' => $inscription,
            'logos' => $logos,
        ]);
        $export->export('SYGAL_convocation_' . $session->getId() . "_" . $inscription->getId() . ".pdf");
    }

    public function genererAttestationAction()
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);
        $session = $inscription->getSession();

        $presences = $this->getPresenceService()->calculerDureePresence($inscription);

        $logos = [];
        try {
            $logos['site'] = $this->fichierStorageService->getFileForLogoStructure($session->getSite());
        } catch (StorageAdapterException $e) {
            $logos['site'] = null;
        }
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $logos['comue'] = $this->fichierStorageService->getFileForLogoStructure($comue);
            } catch (StorageAdapterException $e) {
                $logos['comue'] = null;
            }
        }

        $signature = $this->findSignatureEtablissement($inscription->getDoctorant()->getEtablissement());

        //exporter
        $export = new AttestationExporter($this->renderer, 'A4');
        $export->setVars([
            'signature' => $signature,
            'inscription' => $inscription,
            'logos' => $logos,
            'presences' => $presences,
        ]);
        $export->export('SYGAL_attestation_' . $session->getId() . "_" . $inscription->getId() . ".pdf");
    }

    private function findSignatureEtablissement(Etablissement $etablissementDoctorant): ?string
    {
        $fichier = $this->structureDocumentService->findDocumentFichierForStructureNatureAndEtablissement(
            $etablissementDoctorant->getStructure(),
            NatureFichier::CODE_SIGNATURE_FORMATION,
            $etablissementDoctorant);

        if ($fichier === null) {
            return null;
        }

        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
            return $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération de la signature !", 0, $e);
        }
    }

    /** INSCRIPTION ET DESINSCRIPTION POUR LE DOCTORANT ***************************************************************/

    public function desinscriptionAction() : ViewModel
    {
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $raison = ($data['justification-oui'] AND trim($data['justification-oui']) !== '')? trim($data['justification-oui']) : null;
            $inscription->setListe(null);
            $inscription->setDescription($inscription->getDescription() . " <br/> ". (($raison)?:"Aucune justification"));
            $this->getInscriptionService()->historise($inscription);
        }

        $vm = new ViewModel();
        if ($inscription !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Desinscription de la formation " . $inscription->getSession()->getFormation()->getLibelle(),
                'text' => "La déinscription est définitive. Êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/inscription/desinscription', ["inscription" => $inscription->getId()], [], true),
                'justificationOui' => true,
            ]);
        }
        return $vm;


    }

}