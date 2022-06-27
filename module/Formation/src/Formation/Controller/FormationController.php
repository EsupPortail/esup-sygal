<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Module;
use Formation\Form\Formation\FormationFormAwareTrait;
use Formation\Service\Formation\FormationServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class FormationController extends AbstractController
{
    use EntityManagerAwareTrait;
    use FormationServiceAwareTrait;
    use FormationFormAwareTrait;

    use EtablissementServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        /** Recupération des paramètres du filtres */
        $filtres = [
            'site' => $this->params()->fromQuery('site'),
            'libelle' => $this->params()->fromQuery('libelle'),
            'responsable' => $this->params()->fromQuery('responsable'),
            'modalite' => $this->params()->fromQuery('modalite'),
            'structure' => $this->params()->fromQuery('structure'),
        ];
        /** Listing pour les filtres */
        $listings = [
            'sites' => $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions(),
            'responsables' => $this->getEntityManager()->getRepository(Formation::class)->fetchListeResponsable(),
            'structures' => $this->getEntityManager()->getRepository(Formation::class)->fetchListeStructures(),
        ];

        /** @var Formation[] $formations */
        $formations = $this->getEntityManager()->getRepository(Formation::class)->fetchFormationsWithFiltres($filtres);

        return new ViewModel([
            'formations' => $formations,
            'filtres' => $filtres,
            'listings' => $listings,
        ]);
    }

    public function afficherAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        return new ViewModel([
            'formation' => $formation,
        ]);
    }

    public function ajouterAction()
    {
        $module = $this->getEntityManager()->getRepository(Module::class)->getRequestedModule($this);
        $formation = new Formation();

        if ($module !== null) $formation->setModule($module);

        $form = $this->getFormationForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/ajouter', ['module' => ($module)?$module->getId():null], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getFormationService()->create($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function modifierAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        $form = $this->getFormationForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/modifier', [], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getFormationService()->update($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'une formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function historiserAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        $this->getFormationService()->historise($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) {
            return $this->redirect()->toUrl($retour);
        }
        return $this->redirect()->toRoute('formation/formation');

    }

    public function restaurerAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        $this->getFormationService()->restore($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) {
            return $this->redirect()->toUrl($retour);
        }
        return $this->redirect()->toRoute('formation/formation');
    }

    public function supprimerAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getFormationService()->delete($formation);
            exit();
        }

        $vm = new ViewModel();
        if ($formation !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression d'une formation #" . $formation->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/formation/supprimer', ["formation" => $formation->getId()], [], true),
            ]);
        }
        return $vm;
    }
}