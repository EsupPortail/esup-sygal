<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\AnneeUniv;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Formation\Entity\Db\Module;
use Formation\Form\Module\ModuleFormAwareTrait;
use Formation\Service\Formation\FormationServiceAwareTrait;
use Formation\Service\Module\ModuleServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Service\EntityManagerAwareTrait;

class ModuleController extends AbstractController
{
    use EntityManagerAwareTrait;
    use FormationServiceAwareTrait;
    use ModuleServiceAwareTrait;
    use ModuleFormAwareTrait;
    use AnneeUnivServiceAwareTrait;

    public function afficherAction(): ViewModel
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);
        $formations = $this->getFormationService()->getRepository()->fetchFormationsByModule($module, 'libelle', 'asc', true);

        return new ViewModel([
            'module' => $module,
            'formations' => $formations,
        ]);
    }

    public function ajouterAction(): ViewModel
    {
        $module = new Module();

        $form = $this->getModuleForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/module/ajouter', [], [], true));
        $form->bind($module);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getModuleService()->create($module);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'un module de formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function modifierAction(): ViewModel
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);

        $form = $this->getModuleForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/module/modifier', [], [], true));
        $form->bind($module);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getModuleService()->update($module);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'un module de formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function historiserAction(): Response
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);
        $this->getModuleService()->historise($module);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/module');

    }

    public function restaurerAction(): Response
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);
        $this->getModuleService()->restore($module);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/module');
    }

    public function supprimerAction(): ViewModel
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getModuleService()->delete($module);
            exit();
        }

        $vm = new ViewModel();
        if ($module !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression du module de formation #" . $module->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/module/supprimer', ["module" => $module->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function catalogueAction(): ViewModel
    {
        $anneeCourante = $this->anneeUnivService->courante()->getAnneeUnivToString();
        $anneeUniv = empty($this->params()->fromRoute('anneeUniv')) ? $anneeCourante : $this->params()->fromRoute('anneeUniv');
        $annee = AnneeUniv::fromPremiereAnnee((int)$anneeUniv);
        $debut = $this->anneeUnivService->computeDateDebut($annee);
        $fin = $this->anneeUnivService->computeDateFin($annee);

        $modules = $this->moduleService->getRepository()->getModulesCatalogue($debut, $fin);

        // gestion d'anomalies INUTILE !!!!
//        {
//            $liste = $this->getFormationService()->getRepository()->fetchFormationsByModule(null);
//            $catalogue[-1]["module"] = null;
//            $catalogue[-1]["formations"] = $liste;
//        }

        return new ViewModel([
            'modules' => $modules,
            'anneeUniversitaire' => $annee
        ]);
    }
}