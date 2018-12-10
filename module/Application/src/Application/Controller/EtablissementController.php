<?php

namespace Application\Controller;

use Application\Controller\Traits\LogoAwareControllerTrait;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeStructure;
use Application\Form\EtablissementForm;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\View\Model\ViewModel;

/**
 * Class EtablissementController
 */
class EtablissementController extends AbstractController
{
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use FileServiceAwareTrait;
    use LogoAwareControllerTrait;

    /**
     * @var EtablissementForm $etablissementForm
     */
    private $etablissementForm;

    /**
     * @param EtablissementForm $form
     * @return $this
     */
    public function setEtablissementForm(EtablissementForm $form)
    {
        $this->etablissementForm = $form;

        return $this;
    }

    /**
     * L'index récupére :
     * - la liste des établissements
     * - l'établissement sélectionné
     * - la liste des rôles associées à l'établissement
     * - un tableau de tableaux des rôles associés à chaque rôle
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $etablissements = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_ETABLISSEMENT);

        $etablissementsPrincipaux = array_filter($etablissements, function (Etablissement $e) {
            return $e->estMembre();
        });
        $etablissementsExternes = array_filter($etablissements, function (Etablissement $e) {
            return !$e->estMembre();
        });

        return new ViewModel([
            'etablissementsSygal'    => $etablissementsPrincipaux,
            'etablissementsExternes' => $etablissementsExternes,
        ]);
    }

    public function informationAction()
    {
        $id = $this->params()->fromRoute('structure');
        $etablissement = $this->getEtablissementService()->getRepository()->findByStructureId($id);
        if ($etablissement === null) {
            throw new RuntimeException("Aucun établissement ne possède l'identifiant renseigné.");
        }

        $roleListings = [];
        $individuListings = [];
        $roles = $this->getRoleService()->getRolesByStructure($etablissement->getStructure());
        $individus = $this->getRoleService()->getIndividuByStructure($etablissement->getStructure());
        $individuRoles = $this->getRoleService()->getIndividuRoleByStructure($etablissement->getStructure());

        /** @var Role $role */
        foreach ($roles as $role) {
            if (!$role->isTheseDependant()) {
                $roleListings [$role->getLibelle()] = 0;
            }
        }

        /** @var Individu $individu */
        foreach ($individus as $individu) {
            $denomination = $individu->getNomComplet(false, false, false, true, false);
            $individuListings[$denomination] = [];
        }

        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            if (!$individuRole->getRole()->isTheseDependant()) {
                $denomination = $individuRole->getIndividu()->getNomComplet(false, false, false, true, false);
                $role = $individuRole->getRole()->getLibelle();
                $individuListings[$denomination][] = $role;
                $roleListings[$role]++;
            }
        }

        return new ViewModel([
            'etablissement'   => $etablissement,
            'roleListing'     => $roleListings,
            'individuListing' => $individuListings,
            'logoContent'     => $this->structureService->getLogoStructureContent($etablissement),
        ]);

    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ajouterAction()
    {
        if ($data = $this->params()->fromPost()) {

            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            $this->etablissementForm->setData($data);
            if ($this->etablissementForm->isValid()) {
                /** @var Etablissement $etablissement */
                $etablissement = $this->etablissementForm->getData();
                $this->getEtablissementService()->create($etablissement, $this->userContextService->getIdentityDb());

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoEtablissement($file['cheminLogo']['tmp_name'], $etablissement);
                }

                //creation automatique des roles associés à une unité de recherche
                $this->roleService->addRoleByStructure($etablissement);

                $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' créée avec succès");

                $structureId = $etablissement->getStructure()->getId();

                return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId], 'fragment' => $structureId], true);
            }
        }

        $this->etablissementForm->setAttribute('action', $this->url()->fromRoute('etablissement/ajouter'));
        $this->etablissementForm->bind(new Etablissement());

        $viewModel = new ViewModel([
            'form' => $this->etablissementForm,
        ]);
        $viewModel->setTemplate('application/etablissement/modifier');

        return $viewModel;
    }

    public function supprimerAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $etablissement = $this->getEtablissementService()->getRepository()->findByStructureId($structureId);

        $destructeur = $this->userContextService->getIdentityDb();
        $this->getEtablissementService()->deleteSoftly($etablissement, $destructeur);
        $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' supprimé avec succès");

        return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
    }

    public function modifierAction()
    {
        /** @var Etablissement $etablissement */
        $structureId = $this->params()->fromRoute("structure");
        $etablissement = null;
        if ($structureId) {
            $etablissement = $this->getEtablissementService()->getRepository()->findByStructureId($structureId);
        } else {
            $etablissement = new Etablissement();
        }
        $this->etablissementForm->bind($etablissement);

        // si POST alors on revient du formulaire
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            // action d'affacement du logo
            if (isset($data['supprimer-logo'])) {
                $this->supprimerLogoEtablissement();

                return $this->redirect()->toRoute(null, [], [], true);
            }

            // action de modification
            $this->etablissementForm->setData($data);
            if ($this->etablissementForm->isValid()) {

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoEtablissement($file['cheminLogo']['tmp_name']);
                }
                // mise à jour des données relatives aux écoles doctorales
                $etablissement = $this->etablissementForm->getData();
                $this->getEtablissementService()->update($etablissement);

                $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' modifiée avec succès");

                return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");

            return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
        }

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form' => $this->etablissementForm,
            'logoContent' => $this->structureService->getLogoStructureContent($etablissement),
        ]);
        $viewModel->setTemplate('application/etablissement/modifier');

        return $viewModel;

    }

    public function restaurerAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $etablissement = $this->getEtablissementService()->getRepository()->findByStructureId($structureId);

        $this->getEtablissementService()->undelete($etablissement);

        $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' restauré avec succès");

        return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
    }

    public function supprimerLogoAction()
    {
        $structureId = $this->params()->fromRoute("etablissement");
        $this->supprimerLogoEtablissement();

        return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
    }

    public function supprimerLogoEtablissement()
    {
        $structureId = $this->params()->fromRoute("structure");
        $etablissement = $this->getEtablissementService()->getRepository()->findByStructureId($structureId);

        $this->supprimerLogoStructure($etablissement);
    }

    /**
     * @param string        $cheminLogoUploade chemin vers le fichier temporaire associé au logo
     * @param Etablissement $etablissement
     */
    public function ajouterLogoEtablissement($cheminLogoUploade, Etablissement $etablissement = null)
    {
        if ($etablissement === null) {
            $structureId = $this->params()->fromRoute("structure");
            $etablissement = $this->getEtablissementService()->getRepository()->findByStructureId($structureId);
        }

        $this->ajouterLogoStructure($etablissement, $cheminLogoUploade);
    }
}