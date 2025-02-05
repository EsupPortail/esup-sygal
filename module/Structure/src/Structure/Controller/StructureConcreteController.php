<?php

namespace Structure\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Entity\Db\Variable;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use Fichier\Service\Storage\Adapter\Exception\FileNotFoundInStorageException;
use Individu\Entity\Db\IndividuRole;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Structure\Entity\Db\EcoleDoctorale;
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
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheService;
use UnicaenApp\Exception\RuntimeException;

abstract class StructureConcreteController extends AbstractController
{
    use ApplicationRoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use VariableServiceAwareTrait;

    /**
     * @var string TypeStructure::CODE_ECOLE_DOCTORALE ou
     *             TypeStructure::CODE_UNITE_RECHERCHE ou
     *             TypeStructure::CODE_ETABLISSEMENT
     */
    protected $codeTypeStructure;

    protected string $routeName;
    protected string $routeParamName;

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

    public function indexAction(): ViewModel
    {
        $consultationToutes = $this->isAllowed(
            StructurePrivileges::getResourceId(StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES),
            StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES);

        $structures = [];
        if ($consultationToutes) {
            $qb = $this->structureService->findAllStructuresAffichablesByTypeQb($this->codeTypeStructure, 'structure.libelle');
            $qb // jointure nécessaire pour pouvoir appeler sur chaque enregistrement `estSubstituant()` sans requêtes surnuméraires
                ->addSelect('substitues') /** @see \Substitution\Entity\Db\SubstitutionAwareEntityTrait::estSubstituant() */
                ->leftJoin('structureConcrete.substitues', 'substitues');
            $structures = $qb->getQuery()->getResult();
        } else {
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
    abstract public function voirAction(): ViewModel;

    public function informationAction(): ViewModel
    {
        $id = $this->params()->fromRoute('structure');
        $structureConcrete = $this->getStructureConcreteService()->getRepository()->findByStructureId($id);
        if ($structureConcrete === null) {
            throw new RuntimeException(
                sprintf("Aucune structure de type '%s' trouvée avec l'id %s.", $this->codeTypeStructure, $id));
        }

        $vars = $this->loadInformationForStructure($structureConcrete);

        return new ViewModel($vars);
    }

    protected function loadInformationForStructure(StructureConcreteInterface $structureConcrete): array
    {
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
        $roles = $this->applicationRoleService->findRolesForStructure($structureConcrete->getStructure());
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure($structureConcrete->getStructure());

        foreach ($roles as $role) {
            $roleListings[$role->getLibelle()] = 0;
        }
        foreach ($individuRoles as $individuRole) {
            $role = $individuRole->getRole()->getLibelle();
            $roleListings[$role]++;
        }

        return [
            'structure'       => $structureConcrete,
            'roleListing'     => $roleListings,
            'individuRoles' => $individuRoles,
            'logoContent'     => $this->structureService->getLogoStructureContent($structureConcrete->getStructure()),
        ];
    }

    /**
     * Modifier permet soit d'afficher le formulaire associé à la modification soit de mettre à jour
     * les données associées à une structure (Sigle, Libellé, Code et Logo)
     *
     * TODO en cas de changement de SIGLE ou de CODE penser à faire un renommage du logo
     */
    public function modifierAction(): Response|ViewModel
    {
        $structureConcrete = $this->getRequestedStructureConcrete();
        $this->structureForm->bind($structureConcrete);

        $request = $this->getRequest();

        if ($request->isPost()) {
            // récupération des données et des fichiers
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // action de modification
            $cheminLogo = $structureConcrete->getStructure()->getCheminLogo();
            $this->structureForm->setData($data);
            if ($this->structureForm->isValid()) {
                // sauvegarde du logo si fourni
                if ($data['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoStructure($data['cheminLogo']['tmp_name'], $structureConcrete);
                } else {
                    $structureConcrete->getStructure()->setCheminLogo($cheminLogo);
                }
                // mise à jour des données relatives aux structures
                /** @var EcoleDoctorale|Etablissement|UniteRecherche $structureConcrete */
                $structureConcrete = $this->structureForm->getData();
                $this->getStructureConcreteService()->update($structureConcrete);

                $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' modifiée avec succès");

                return $this->redirect()->toRoute($this->routeName.'/voir', [$this->routeParamName => $structureConcrete->getId()], [], true);
            }

            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saisies.");
        }

        return new ViewModel([
            'structure' => $structureConcrete,
            'form' => $this->structureForm,
            'logoContent' => $this->structureService->getLogoStructureContent($structureConcrete->getStructure()),
        ]);
    }

    /**
     * Retourne la structure concrète (etab/ed/ur) spécifiée dans la requête.
     */
    protected function getRequestedStructureConcrete(): StructureConcreteInterface
    {
        return $this->getStructureConcreteService()->getRepository()->find(
            $this->params()->fromRoute($this->routeParamName)
        );
    }

    public function ajouterAction(): Response|ViewModel
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            // récupération des données et des fichiers
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $this->structureForm->setData($data);
            if ($this->structureForm->isValid()) {
                /** @var EcoleDoctorale|Etablissement|UniteRecherche $structureConcrete */
                $structureConcrete = $this->structureForm->getData();
                $structureConcrete = $this->getStructureConcreteService()->create($structureConcrete, $this->userContextService->getIdentityDb());

                // sauvegarde du logo si fourni
                if ($data['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoStructure($data['cheminLogo']['tmp_name'], $structureConcrete);
                }

                //ajoute à la création de l'établissement d'inscription l'accès au module Admission
                if($structureConcrete instanceof Etablissement && $structureConcrete->estInscription()){
                    $variable = $this->variableService->newVariable($structureConcrete);
                    $variable->setCode(Variable::CODE_UTILISATION_MODULE_ADMISSION);
                    $variable->setDescription("Utilisation ou non du module Admission");
                    $variable->setValeur("true");
                    $this->variableService->create($variable);
                }

                $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' créée avec succès");

                $structureId = $structureConcrete->getStructure()->getId();

                return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureId]], true);
            }
        }

        return new ViewModel([
            'form' => $this->structureForm,
        ]);
    }

    /**
     * @return Response
     */
    public function supprimerAction()
    {
        $structureConcrete = $this->getRequestedStructureConcrete();
        $this->getStructureConcreteService()->deleteSoftly($structureConcrete, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' supprimée avec succès");

        return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureConcrete->getId()]], true);
    }

    /**
     * @return Response
     */
    public function restaurerAction()
    {
        $structureConcrete = $this->getRequestedStructureConcrete();
        $this->getStructureConcreteService()->undelete($structureConcrete);

        $this->flashMessenger()->addSuccessMessage("Structure '$structureConcrete' restaurée avec succès");

        return $this->redirect()->toRoute($this->routeName, [], ['query' => ['selected' => $structureConcrete->getId()]], true);
    }

    public function supprimerLogoAction(): Response
    {
        $structureConcrete = $this->getRequestedStructureConcrete();
        $this->supprimerLogoStructure($structureConcrete);

        return $this->redirect()->toRoute($this->routeName."/voir", [$this->routeParamName => $structureConcrete->getId()], [], true);
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
    protected function supprimerLogoStructure(StructureConcreteInterface $structureConcrete): void
    {
        try {
            // NB : on vise ici la structure liée
            $structure = $structureConcrete->getStructure();
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
     * @param \Structure\Entity\Db\StructureConcreteInterface $structureConcrete
     */
    protected function ajouterLogoStructure(string $cheminLogoUploade, StructureConcreteInterface $structureConcrete)
    {
        try {
            // NB : on vise ici la structure liée
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

        $roles_tmp = $this->applicationRoleService->findRolesForStructure($structure);
        $roles = [];
        /** @var Role $role */
        foreach ($roles_tmp as $role) {
            if (!$role->isTheseDependant()) $roles[] = $role;
        }

        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure($structure);

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
    public function genererRolesDefautsAction()
    {
        $id   = $this->params()->fromRoute('id');
        $type = $this->params()->fromRoute('type');

        switch($type) {
            case TypeStructure::CODE_ECOLE_DOCTORALE :
                $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($id);
                $this->getApplicationRoleService()->addRoleByStructure($ecole);
                $this->redirect()->toRoute('ecole-doctorale/voir', ['ecole-doctorale' => $ecole->getId()], ['query' => ['tab' => StructureController::TAB_membres]], true);
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE :
                $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($id);
                $this->getApplicationRoleService()->addRoleByStructure($unite);
                $this->redirect()->toRoute('unite-recherche/voir', ['unite-recherche' => $unite->getId()], ['query' => ['tab' => StructureController::TAB_membres]], true);
                break;
            case TypeStructure::CODE_ETABLISSEMENT :
                $etab = $this->getEtablissementService()->getRepository()->findByStructureId($id);
                $this->getApplicationRoleService()->addRoleByStructure($etab);
                $this->redirect()->toRoute('etablissement/voir', ['etablissement' => $etab->getId()], ['query' => ['tab' => StructureController::TAB_membres]], true);
                break;
        }
    }

}