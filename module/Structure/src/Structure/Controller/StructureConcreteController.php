<?php

namespace Structure\Controller;

use Application\Controller\AbstractController;
use Structure\Entity\Db\EcoleDoctorale;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Structure\Form\EcoleDoctoraleForm;
use Structure\Form\EtablissementForm;
use Structure\Form\UniteRechercheForm;
use Structure\Provider\Privilege\StructurePrivileges;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheService;
use BjyAuthorize\Exception\UnAuthorizedException;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

abstract class StructureConcreteController extends AbstractController
{
    use RoleServiceAwareTrait;
    use StructureServiceAwareTrait;

    /**
     * @var string TypeStructure::CODE_ECOLE_DOCTORALE ou
     *             TypeStructure::CODE_UNITE_RECHERCHE ou
     *             TypeStructure::CODE_ETABLISSEMENT
     */
    protected $codeTypeStructure;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var UniteRechercheForm|EcoleDoctoraleForm|EtablissementForm
     */
    protected $structureForm;

    /**
     * @param UniteRechercheForm|EcoleDoctoraleForm|EtablissementForm $form
     */
    public function setStructureForm($form)
    {
        $this->structureForm = $form;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $consultationToutes = $this->isAllowed(
            StructurePrivileges::getResourceId(StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES),
            StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES);

        $structures = [];
        if ($consultationToutes) {
            $structures = $this->structureService->findAllStructuresAffichablesByType($this->codeTypeStructure, 'libelle');
        } else {
            /** @var Role $role*/
            $role = $this->userContextService->getSelectedIdentityRole();
            if ($role->isEcoleDoctoraleDependant() || $role->isUniteRechercheDependant() || $role->isEtablissementDependant()) {
                $structure = $this->getStructureConcreteService()->getRepository()->findByStructureId($role->getStructure()->getId());
                $structures[] = $structure;
            }
        }

        return new ViewModel([
            'structures' => $structures,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function informationAction(): ViewModel
    {
        $id = $this->params()->fromRoute('structure');
        $structureConcrete = $this->getStructureConcreteService()->getRepository()->findByStructureId($id);
        if ($structureConcrete === null) {
            throw new RuntimeException(
                sprintf("Aucune structure de type '%s' trouvée avec l'id %s.", $this->codeTypeStructure, $id));
        }

        $consultationToutes = $this->isAllowed(
            StructurePrivileges::getResourceId(StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES),
            StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES);
        $consultationSes    = $this->isAllowed(
            $structureConcrete->getStructure(),
            StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES);

        if (! $consultationToutes && ! $consultationSes) {
            throw new UnAuthorizedException("Vous ne disposez pas des privilèges vous permettant d'accéder aux informations de cette structure.");
        }

        $roleListings = [];
        $roles = $this->roleService->findRolesForStructure($structureConcrete->getStructure());
        $individuRoles = $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure());

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleListings[$role->getLibelle()] = 0;
        }
        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            $role = $individuRole->getRole()->getLibelle();
            $roleListings[$role]++;
        }

        return new ViewModel([
            'structure'       => $structureConcrete,
            'roleListing'     => $roleListings,
            'individuRoles' => $individuRoles,
            'logoContent'     => $this->structureService->getLogoStructureContent($structureConcrete->getStructure()),
        ]);
    }

    /**
     * Modifier permet soit d'afficher le formulaire associé à la modification soit de mettre à jour
     * les données associées à une structure (Sigle, Libellé, Code et Logo)
     *
     * @return Response|ViewModel
     *
     * TODO en cas de changement de SIGLE ou de CODE penser à faire un renommage du logo
     */
    public function modifierAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $structureConcrete  = $this->getStructureConcreteService()->getRepository()->findByStructureId($structureId);
        $this->structureForm->bind($structureConcrete);

        $request = $this->getRequest();

        if ($request->isPost()) {
            // récupération des données et des fichiers
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            // action de modification
            $cheminLogo = $structureConcrete->getStructure()->getCheminLogo();
            $this->structureForm->setData($data);
            if ($this->structureForm->isValid()) {

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoStructure($file['cheminLogo']['tmp_name']);
                } else {
                    $structureConcrete->getStructure()->setCheminLogo($cheminLogo);
                }
                // mise à jour des données relatives aux structures
                /** @var EcoleDoctorale|Etablissement|UniteRecherche $structureConcrete */
                $structureConcrete = $this->structureForm->getData();
                $this->getStructureConcreteService()->update($structureConcrete);

                $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' modifiée avec succès");
                $test = $this->routeName .'/information';
                return $this->redirect()->toRoute($this->routeName.'/information', ['structure' => $structureId], [], true);
            }

            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saisies.");

            //return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId]], true);
        }

        return new ViewModel([
            'structure' => $structureConcrete,
            'form' => $this->structureForm,
            'logoContent' => $this->structureService->getLogoStructureContent($structureConcrete->getStructure()),
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function ajouterAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            // récupération des données et des fichiers
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            $this->structureForm->setData($data);
            if ($this->structureForm->isValid()) {
                /** @var EcoleDoctorale|Etablissement|UniteRecherche $structureConcrete */
                $structureConcrete = $this->structureForm->getData();
                $structureConcrete = $this->getStructureConcreteService()->create($structureConcrete, $this->userContextService->getIdentityDb());

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoStructure($file['cheminLogo']['tmp_name'], $structureConcrete);
                }

                // creation automatique des roles associés à une structure
                $this->roleService->addRoleByStructure($structureConcrete);

                $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' créée avec succès");

                $structureId = $structureConcrete->getStructure()->getId();

                return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId]], true);
            }
        }

        $this->structureForm->setAttribute('action', $this->url()->fromRoute($this->routeName . '/ajouter'));

        return new ViewModel([
            'form' => $this->structureForm,
        ]);
    }

    /**
     * @return Response
     */
    public function supprimerAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $structureConcrete  = $this->getStructureConcreteService()->getRepository()->findByStructureId($structureId);

        $this->getStructureConcreteService()->deleteSoftly($structureConcrete, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' supprimée avec succès");

        return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId]], true);
    }

    /**
     * @return Response
     */
    public function restaurerAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $structureConcrete  = $this->getStructureConcreteService()->getRepository()->findByStructureId($structureId);

        $this->getStructureConcreteService()->undelete($structureConcrete);

        $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' restaurée avec succès");

        return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId]], true);
    }

    /**
     * @return Response
     */
    public function supprimerLogoAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $this->supprimerLogoStructure();

        return $this->redirect()->toRoute($this->routeName."/information", [], ['query' => ['selected' => $structureId]], true);
    }

    /**
     * @return EtablissementService|EcoleDoctoraleService|UniteRechercheService
     */
    abstract protected function getStructureConcreteService();

    /**
     * Retire le logo associé à une structure :
     * - effacement du chemin en bdd,
     * - effacement du fichier stocké sur le serveur.
     */
    protected function supprimerLogoStructure()
    {
        $structureId = $this->params()->fromRoute("structure");
        $structureConcrete  = $this->getStructureConcreteService()->getRepository()->findByStructureId($structureId);

        try {
            // NB : on vise ici la structure liée originale, pas son éventuelle structure substituante.
            $structure = $structureConcrete->getStructure(false);
            $fileDeleted = $this->structureService->deleteLogoStructure($structure);
        } catch (RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage(
                "Erreur lors de l'effacement du logo de la structure '$structureConcrete' : " . $e->getMessage());
            return;
        }

        if ($fileDeleted) {
            $this->flashMessenger()->addSuccessMessage("Le logo de la structure '$structureConcrete' vient d'être supprimé.");
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour la structure '$structureConcrete'.");
        }
    }

    /**
     * Ajoute le logo associé à une structure :
     * - suppression du précédent logo éventuel,
     * - modification du chemin en bdd
     * - création du fichier sur le serveur.
     *
     * @param string $cheminLogoUploade chemin vers le fichier temporaire associé au logo
     * @param \Structure\Entity\Db\StructureConcreteInterface|null $structureConcrete
     */
    protected function ajouterLogoStructure(string $cheminLogoUploade, StructureConcreteInterface $structureConcrete = null)
    {
        if ($structureConcrete === null) {
            $structureId = $this->params()->fromRoute("structure");
            $structureConcrete  = $this->getStructureConcreteService()->getRepository()->findByStructureId($structureId);
        }

        try {
            // NB : on vise ici la structure liée originale, pas son éventuelle structure substituante.
            $structure = $structureConcrete->getStructure(false);
            $this->structureService->updateLogoStructure($structure, $cheminLogoUploade);
        } catch (RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage(
                "Erreur lors de l'enregistrement du logo de la structure '$structureConcrete' : " . $e->getMessage());
        }

        $this->flashMessenger()->addSuccessMessage("Le logo de la structure '$structureConcrete' vient d'être ajouté.");
    }

    /**
     * @return ViewModel
     */
    public function individuRoleAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $structure = $this->structureService->findStructureById($structureId);
        $type = $this->params()->fromRoute("type");

        $roles_tmp = $this->roleService->findRolesForStructure($structure);
        $roles = [];
        /** @var Role $role */
        foreach ($roles_tmp as $role) {
            if (!$role->isTheseDependant()) $roles[] = $role;
        }

        $individuRoles = $this->roleService->findIndividuRoleByStructure($structure);

        $repartition = [];
        foreach ($roles as $role) {
            $repartition[$role->getId()] = [];
        }

        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            $role = $individuRole->getRole();
            $individu = $individuRole->getIndividu();
            $repartition[$role->getId()][] = $individu;
        }

        $membres = [];
        foreach ($repartition as $role => $individus) {
            $membres = array_merge($membres, $individus);
        }
        $membres = array_unique($membres);

        return new ViewModel([
            'roles' => $roles,
            'membres' => $membres,
            'repartition' => $repartition,
            'type' => $type,
        ]);
    }


    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function genererRolesDefautsAction() {
        $id   = $this->params()->fromRoute('id');
        $type = $this->params()->fromRoute('type');

        switch($type) {
            case TypeStructure::CODE_ECOLE_DOCTORALE :
                $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($ecole);
                $this->redirect()->toRoute('ecole-doctorale/information', ['ecoleDoctorale' => $id], ['query' => ['tab' => StructureController::TAB_membres]], true);
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE :
                $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($unite);
                $this->redirect()->toRoute('unite-recherche/information', ['uniteRecherche' => $id], ['query' => ['tab' => StructureController::TAB_membres]], true);
                break;
            case TypeStructure::CODE_ETABLISSEMENT :
                $unite = $this->getEtablissementService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($unite);
                $this->redirect()->toRoute('etablissement/information', ['etablissement' => $id], ['query' => ['tab' => StructureController::TAB_membres]], true);
                break;
        }
    }

}