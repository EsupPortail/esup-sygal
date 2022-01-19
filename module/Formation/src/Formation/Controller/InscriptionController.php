<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Individu;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Service\Exporter\Attestation\AttestationExporter;
use Formation\Service\Exporter\Convocation\ConvocationExporter;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Formation\Service\Presence\PresenceServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class InscriptionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use DoctorantServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FileServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use PresenceServiceAwareTrait;

    /** @var PhpRenderer */
    private $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    public function indexAction()
    {
        $filtres = [
            'session' => $this->params()->fromQuery('session'),
            'doctorant' => $this->params()->fromQuery('doctorant'),
            'liste' => $this->params()->fromQuery('liste'),
        ];
        $listings = [
        ];
        /** @var Inscription[] $inscriptions */
        $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->fetchInscriptionsWithFiltres($filtres);

        return new ViewModel([
            'inscriptions' => $inscriptions,
            'filtres' => $filtres,
            'listings' => $listings,
        ]);
    }

    public function ajouterAction()
    {
        /** @var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);
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

    public function historiserAction()
    {
        /** @var Inscription $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getInscriptionService()->historise($inscription);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function restaurerAction()
    {
        /** @var Inscription $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getInscriptionService()->restore($inscription);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function supprimerAction()
    {
        /** @var Inscription|null $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);

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

    public function passerListePrincipaleAction()
    {
        /** @var Inscription|null $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        $retour = $this->params()->fromQuery('retour');

        $session = $inscription->getSession();

        $listePrincipale = $session->getListePrincipale();
        if (count($listePrincipale) < $session->getTailleListePrincipale()) {
            $inscription->setListe(Inscription::LISTE_PRINCIPALE);
            $this->getInscriptionService()->update($inscription);
        } else {
            $this->flashMessenger()->addErrorMessage('La liste principale est déjà complète.');
        }

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function passerListeComplementaireAction()
    {
        /** @var Inscription|null $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        $retour = $this->params()->fromQuery('retour');

        $session = $inscription->getSession();

        $listePrincipale = $session->getListeComplementaire();
        if (count($listePrincipale) < $session->getTailleListeComplementaire()) {
            $inscription->setListe(Inscription::LISTE_COMPLEMENTAIRE);
            $this->getInscriptionService()->update($inscription);
        } else {
            $this->flashMessenger()->addErrorMessage('La liste complémentaire est déjà complète.');
        }

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function retirerListeAction()
    {
        /** @var Inscription|null $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        $retour = $this->params()->fromQuery('retour');

        $inscription->setListe(null);
        $this->getInscriptionService()->update($inscription);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/inscription',[],[], true);
    }

    public function genererConvocationAction()
    {
        /** @var Inscription|null $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        $session = $inscription->getSession();

        $logos = [];
        $logos['site'] = $this->fileService->computeLogoFilePathForStructure($session->getSite());
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            $logos['comue'] = $this->fileService->computeLogoFilePathForStructure($comue);
        }

        //exporter
        $export = new ConvocationExporter($this->renderer, 'A4');
        $export->setVars([
            'inscription' => $inscription,
            'logos' => $logos,
        ]);
        $export->export('SYGAL_convocation_' . $session->getId() . "_" . $inscription->getId() . ".pdf");
    }

    public function genererAttestationAction()
    {
        /** @var Inscription|null $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        $session = $inscription->getSession();

        $presences = $this->getPresenceService()->calculerDureePresence($inscription);

        $logos = [];
        $logos['site'] = $this->fileService->computeLogoFilePathForStructure($session->getSite());
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            $logos['comue'] = $this->fileService->computeLogoFilePathForStructure($comue);
        }

        //exporter
        $export = new AttestationExporter($this->renderer, 'A4');
        $export->setVars([
            'inscription' => $inscription,
            'logos' => $logos,
            'presences' => $presences,
        ]);
        $export->export('SYGAL_attestation_' . $session->getId() . "_" . $inscription->getId() . ".pdf");
    }
}