<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Formation\Entity\Db\Seance;
use Formation\Form\Seance\SeanceFormAwareTrait;
use Formation\Service\Exporter\Emargement\EmargementExporter;
use Formation\Service\Seance\SeanceServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SeanceController extends AbstractController
{
    use EntityManagerAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use SeanceServiceAwareTrait;
    use SessionServiceAwareTrait;
    use SeanceFormAwareTrait;

    private ?PhpRenderer $renderer = null;
    public function setRenderer(PhpRenderer $renderer) { $this->renderer = $renderer; }

    public function indexAction() : ViewModel
    {
        $seances = $this->getSeanceService()->getRepository()->findAll();

        return new ViewModel([
            'seances' => $seances,
        ]);
    }

    public function ajouterAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        if ($session === null) {
            throw new RuntimeException("Aucune session pour ajouter la séance");
        }

        $seance = new Seance();
        $seance->setSession($session);

        $form = $this->getSeanceForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/seance/ajouter', ['session' => $session->getId()], [], true));
        $form->bind($seance);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSeanceService()->create($seance);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une séance pour la session de formation #".$session->getId(),
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $seance = $this->getSeanceService()->getRepository()->getRequestedSeance($this);

        $form = $this->getSeanceForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/seance/modifier', ['seance' => $seance->getId()], [], true));
        $form->bind($seance);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSeanceService()->update($seance);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une séance pour la seance de formation #".$seance->getId(),
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function historiserAction() : Response
    {
        $seance = $this->getSeanceService()->getRepository()->getRequestedSeance($this);
        $this->getSeanceService()->historise($seance);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/seance');

    }

    public function restaurerAction() : Response
    {
        $seance = $this->getSeanceService()->getRepository()->getRequestedSeance($this);
        $this->getSeanceService()->restore($seance);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/seance');
    }

    public function supprimerAction() : ViewModel
    {
        $seance = $this->getSeanceService()->getRepository()->getRequestedSeance($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getSeanceService()->delete($seance);
            exit();
        }

        $vm = new ViewModel();
        if ($seance !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la seance #" . $seance->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/seance/supprimer', ["seance" => $seance->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function genererEmargementAction()
    {
        $seance = $this->getSeanceService()->getRepository()->getRequestedSeance($this);
        $session = $seance->getSession();

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

        //exporter
        $export = new EmargementExporter($this->renderer, 'A4');
        $export->setVars([
            'seance' => $seance,
            'logos' => $logos,
        ]);
        $export->export('SYGAL_emargement_' . $session->getId() . "_" . $seance->getId() . ".pdf");
    }
}