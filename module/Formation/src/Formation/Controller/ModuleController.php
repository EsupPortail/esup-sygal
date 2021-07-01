<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Module;
use Formation\Form\Module\ModuleFormAwareTrait;
use Formation\Service\Module\ModuleServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class ModuleController extends AbstractController
{
    use EntityManagerAwareTrait;
    use ModuleServiceAwareTrait;
    use ModuleFormAwareTrait;

    public function indexAction()
    {
        /** @var Module[] $formations */
        $modules = $this->getEntityManager()->getRepository(Module::class)->findAll();

        return new ViewModel([
            'modules' => $modules,
        ]);
    }

    public function afficherAction()
    {
        /** @var Module|null $module */
        $module = $this->getEntityManager()->getRepository(Module::class)->getRequestedModule($this);

        return new ViewModel([
            'module' => $module,
        ]);
    }

    public function ajouterAction()
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
        $vm->setTemplate('formation/module/modifier');
        return $vm;
    }

    public function modifierAction()
    {
        /** @var Module|null $module */
        $module = $this->getEntityManager()->getRepository(Module::class)->getRequestedModule($this);

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
        $vm->setTemplate('formation/module/modifier');
        return $vm;
    }

    public function historiserAction()
    {
        /** @var Module|null $module */
        $module = $this->getEntityManager()->getRepository(Module::class)->getRequestedModule($this);

        $this->getModuleService()->historise($module);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) {
            return $this->redirect()->toUrl($retour);
        }
        return $this->redirect()->toRoute('formation/module');

    }

    public function restaurerAction()
    {
        /** @var Module|null $module */
        $module = $this->getEntityManager()->getRepository(Module::class)->getRequestedModule($this);

        $this->getModuleService()->restore($module);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) {
            return $this->redirect()->toUrl($retour);
        }
        return $this->redirect()->toRoute('formation/module');
    }

    public function supprimerAction()
    {
        /** @var Module|null $module */
        $module = $this->getEntityManager()->getRepository(Module::class)->getRequestedModule($this);

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
}