<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
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

    use EtablissementServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        /** Recupération des paramètres du filtres */
        $site = $this->params()->fromQuery('site');
        $libelle = $this->params()->fromQuery('libelle');
        $responsable = $this->params()->fromQuery('responsable');
        $modalite = $this->params()->fromQuery('modalite');
        $structure = $this->params()->fromQuery('structure');

        /** Listing pour les filtres */
        $sites = $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions();
        $responsables = $this->getEntityManager()->getRepository(Module::class)->fetchListeResponsable();
        $structures = $this->getEntityManager()->getRepository(Module::class)->fetchListeStructures();

        /** @var Module[] $formations */ //todo appliquer les filtre dans le repository
        $modules = $this->getEntityManager()->getRepository(Module::class)->findAll();
        if ($site !== null AND $site !== '') $modules = array_filter($modules, function(Module $a) use ($site) { return $a->getSite()->getCode() === $site;});
        if ($libelle !== null AND $libelle !== '') $modules = array_filter($modules, function(Module $a) use ($libelle) { return str_contains(strtolower($a->getLibelle()), strtolower($libelle));});
        if ($responsable !== null AND $responsable !== '') $modules = array_filter($modules, function(Module $a) use ($responsable) { return $a->getResponsable()->getId() == $responsable;});
        if ($structure !== null AND $structure !== '') $modules = array_filter($modules, function(Module $a) use ($structure) { return ($a->getTypeStructure() === null OR $a->getTypeStructure()->getId() == $structure);});
        if ($modalite !== null AND $modalite !== '') $modules = array_filter($modules, function(Module $a) use ($modalite) { return ($a->getModalite() === null OR $a->getModalite() === $modalite);});

        return new ViewModel([
            'modules' => $modules,
            //valeurs sélectionnées
            'site' => $site,
            'libelle' => $libelle,
            'responsable' => $responsable,
            'modalite' => $modalite,
            'structure' => $structure,
            //données pour le filtre
            'sites' => $sites,
            'responsables' => $responsables,
            'structures' => $structures,
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