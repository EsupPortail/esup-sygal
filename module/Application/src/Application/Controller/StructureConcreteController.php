<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Form\EcoleDoctoraleForm;
use Application\Form\EtablissementForm;
use Application\Form\UniteRechercheForm;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\SourceCodeStringHelperAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

abstract class StructureConcreteController extends AbstractController
{
    use RoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

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
     * @return ViewModel
     */
    public function indexAction()
    {
        $consultationToutes = $this->isAllowed(
            StructurePrivileges::getResourceId(StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES),
            StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES);

        $structures = [];
        if ($consultationToutes) {
            $structures = $this->structureService->getAllStructuresAffichablesByType($this->codeTypeStructure, 'libelle');
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
    public function informationAction()
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
        $individuListings = [];
        $roles = $this->roleService->getRolesByStructure($structureConcrete->getStructure());
        $individus = $this->roleService->getIndividuByStructure($structureConcrete->getStructure());
        $individuRoles = $this->roleService->getIndividuRoleByStructure($structureConcrete->getStructure());

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleListings[$role->getLibelle()] = 0;
        }

        /** @var Individu $individu */
        foreach ($individus as $individu) {
            $denomination = $individu->getNomComplet(false, false, false, true, false);
            $individuListings[$denomination] = [];
        }

        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            $denomination = $individuRole->getIndividu()->getNomComplet(false, false, false, true, false);
            $role = $individuRole->getRole()->getLibelle();
            $individuListings[$denomination][] = $role;
            $roleListings[$role]++;
        }

        return new ViewModel([
            'structure'       => $structureConcrete,
            'roleListing'     => $roleListings,
            'individuListing' => $individuListings,
            'logoContent'     => $this->structureService->getLogoStructureContent($structureConcrete),
        ]);
    }

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
     * Modifier permet soit d'afficher le formulaire associé à la modification soit de mettre à jour
     * les données associées à une structure (Sigle, Libellé, Code et Logo)
     *
     * @return Response|ViewModel
     *
     * TODO en cas de changement de SIGLE ou de CODE penser à faire un renommage du logo
     */
    public function modifierAction()
    {
        /** @var UniteRecherche $structureConcrete */
        $structureId = $this->params()->fromRoute("structure");
        $structureConcrete  = $this->getStructureConcreteService()->getRepository()->findByStructureId($structureId);
        $this->structureForm->bind($structureConcrete);

        // si POST alors on revient du formulaire
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            // action de modification
            $cheminLogo = $structureConcrete->getCheminLogo();
            $this->structureForm->setData($data);
            if ($this->structureForm->isValid()) {

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoStructure($file['cheminLogo']['tmp_name']);
                } else {
                    $structureConcrete->setCheminLogo($cheminLogo);
                }
                // mise à jour des données relatives aux structures
                $structureConcrete = $this->structureForm->getData();
                $this->getStructureConcreteService()->update($structureConcrete);

                $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' modifiée avec succès");
                $test = $this->routeName .'/information';
                return $this->redirect()->toRoute($this->routeName.'/information', ['structure' => $structureId], [], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");

            return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId], "fragment" => "" . $structureId], true);
        }

        $viewModel = new ViewModel([
            'structure' => $structureConcrete,
            'form'                        => $this->structureForm,
//            'etablissements'              => $etablissements,
//            'etablissementsRattachements' => $etablissementsRattachements,
//            'domainesAssocies'            => $unite->getDomaines(),
//            'domainesScientifiques'       => $domaineScientifiques,
            'logoContent'                 => $this->structureService->getLogoStructureContent($structureConcrete),
        ]);

        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     */
    public function ajouterAction()
    {
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            $this->structureForm->setData($data);
            if ($this->structureForm->isValid()) {
                /** @var EcoleDoctorale $structureConcrete */
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

                return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
            }
        }

        $this->structureForm->setAttribute('action', $this->url()->fromRoute($this->routeName . '/ajouter'));

        $viewModel = new ViewModel([
            'form' => $this->structureForm,
        ]);

        return $viewModel;
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

        return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
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

        return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
    }

    /**
     * @return Response
     */
    public function supprimerLogoAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $this->supprimerLogoStructure();

        return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
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
        $structure  = $this->getStructureConcreteService()->getRepository()->findByStructureId($structureId);

        try {
            $fileDeleted = $this->structureService->deleteLogoStructure($structure);
        } catch (RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage(
                "Erreur lors de l'effacement du logo de la structure '$structure' : " . $e->getMessage());
            return;
        }

        if ($fileDeleted) {
            $this->flashMessenger()->addSuccessMessage("Le logo de la structure '$structure' vient d'être supprimé.");
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour la structure '$structure'.");
        }
    }

    /**
     * Ajoute le logo associé à une structure :
     * - suppression du précédent logo éventuel,
     * - modification du chemin en bdd
     * - création du fichier sur le serveur.
     *
     * @param string                     $cheminLogoUploade chemin vers le fichier temporaire associé au logo
     * @param StructureConcreteInterface $structure
     */
    protected function ajouterLogoStructure($cheminLogoUploade, StructureConcreteInterface $structure = null)
    {
        if ($structure === null) {
            $structureId = $this->params()->fromRoute("structure");
            $structure  = $this->getStructureConcreteService()->getRepository()->findByStructureId($structureId);
        }

        try {
            $this->structureService->updateLogoStructure($structure, $cheminLogoUploade);
        } catch (RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage(
                "Erreur lors de l'enregistrement du logo de la structure '$structure' : " . $e->getMessage());
        }

        $this->flashMessenger()->addSuccessMessage("Le logo de la structure '$structure' vient d'être ajouté.");
    }

    /**
     * @return ViewModel
     */
    public function individuRoleAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $structure = $this->structureService->findStructureById($structureId);
        $type = $this->params()->fromRoute("type");

        $roles_tmp = $this->roleService->getRolesByStructure($structure);
        $roles = [];
        /** @var Role $role */
        foreach ($roles_tmp as $role) {
            if (!$role->isTheseDependant()) $roles[] = $role;
        }

        $individuRoles = $this->roleService->getIndividuRoleByStructure($structure);

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
                $this->redirect()->toRoute('ecole-doctorale/information', ['ecoleDoctorale' => $id], [], true);
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE :
                $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($unite);
                $this->redirect()->toRoute('unite-recherche/information', ['uniteRecherche' => $id], [], true);
                break;
            case TypeStructure::CODE_ETABLISSEMENT :
                $unite = $this->getEtablissementService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($unite);
                $this->redirect()->toRoute('etablissement/information', ['etablissement' => $id], [], true);
                break;
        }
    }

}