<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Module;
use Formation\Form\Module\ModuleFormAwareTrait;
use Formation\Service\Module\ModuleServiceAwareTrait;
use Laminas\Http\Response;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class ModuleController extends AbstractController {
    use EntityManagerAwareTrait;
    use ModuleServiceAwareTrait;
    use ModuleFormAwareTrait;

    public function afficherAction()
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);

        return new ViewModel([
            'module' => $module,
        ]);
    }

    public function ajouterAction() : ViewModel
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

    public function modifierAction() : ViewModel
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

    public function historiserAction() : Response
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);
        $this->getModuleService()->historise($module);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/module');

    }

    public function restaurerAction() : Response
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);
        $this->getModuleService()->restore($module);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/module');
    }

    public function supprimerAction() : ViewModel
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

    public function catalogueAction() : ViewModel
    {
        /** @var Module[] $modules */
        $modules = $this->getEntityManager()->getRepository(Module::class)->findAll();
        usort($modules, function (Module $a, Module $b) { return $a->getLibelle() > $b->getLibelle(); });
        /** @var Formation[] $formations */
        $formations = $this->getEntityManager()->getRepository(Formation::class)->findAll();

        $catalogue = [];
        foreach ($modules as $module) {
            if ($module->estNonHistorise()) {
                $liste = [];
                foreach ($formations as $formation) {
                    if ($formation->estNonHistorise() AND $formation->getModule() === $module) {
                        $liste[] = $formation;
                    }
                }
                usort($liste, function (Formation $a, Formation $b) { return $a->getLibelle() > $b->getLibelle();});

                $catalogue[$module->getId()]["module"] = $module;
                $catalogue[$module->getId()]["formations"] = $liste;
            }
        }

        {
            $liste = [];
            foreach ($formations as $formation) {
                if ($formation->estNonHistorise() AND $formation->getModule() === null) {
                    $liste[] = $formation;
                }
            }
            usort($liste, function (Formation $a, Formation $b) { return $a->getLibelle() > $b->getLibelle();});

            $catalogue[-1]["module"] = null;
            $catalogue[-1]["formations"] = $liste;
        }

        return new ViewModel([
            'catalogue' => $catalogue
        ]);
    }
}