<?php

namespace Application\Controller;

use Application\Controller\Traits\LogoAwareControllerTrait;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Form\UniteRechercheForm;
use Application\Service\DomaineScientifiqueServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\View\Model\ViewModel;

class UniteRechercheController extends AbstractController
{
    use UniteRechercheServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;
    use DomaineScientifiqueServiceAwareTrait;
    use FileServiceAwareTrait;
    use LogoAwareControllerTrait;

    /**
     * L'index récupére :
     * - la liste des unités de recherches
     * - l'unité sélectionnée
     * - la liste des rôles associées à l'unité
     * - un tableau de tableaux des rôles associés à chaque rôle
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $urs = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle');

        return new ViewModel([
            'unites' => $urs,
        ]);
    }

    public function informationAction()
    {
        $id = $this->params()->fromRoute('uniteRecherche');
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($id);
        if ($unite === null) {
            throw new RuntimeException("Aucune unité de recherche ne possède l'identifiant renseigné.");
        }

        $roleListings = [];
        $individuListings = [];
        $roles = $this->getRoleService()->getRolesByStructure($unite->getStructure());
        $individus = $this->getRoleService()->getIndividuByStructure($unite->getStructure());
        $individuRoles = $this->getRoleService()->getIndividuRoleByStructure($unite->getStructure());

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleListings [$role->getLibelle()] = 0;
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

        $etablissementsRattachements = $this->getUniteRechercheService()->findEtablissementRattachement($unite);

        return new ViewModel([
            'unite'                       => $unite,
            'roleListing'                 => $roleListings,
            'individuListing'             => $individuListings,
            'etablissementsRattachements' => $etablissementsRattachements,
            'logoContent'                 => $this->structureService->getLogoStructureContent($unite),
        ]);
    }

    /**
     * Modifier permet soit d'afficher le formulaire associé à la modification soit de mettre à jour
     * les données associées à une unité de recherche (Sigle, Libellé, Code et Logo)
     *
     * @return \Zend\Http\Response|ViewModel
     *
     * TODO en cas de changement de SIGLE ou de CODE penser à faire un renommage du logo
     */
    public function modifierAction()
    {
        /** @var UniteRecherche $unite */
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        $this->uniteRechercheForm->bind($unite);

        // si POST alors on revient du formulaire
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            // action d'affacement du logo
            if (isset($data['supprimer-logo'])) {
                $this->supprimerLogoUniteRecherche();

                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => $uniteId], true);
            }

            // action de modification
            $this->uniteRechercheForm->setData($data);
            if ($this->uniteRechercheForm->isValid()) {

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoUniteRecherche($file['cheminLogo']['tmp_name']);
                }
                // mise à jour des données relatives aux unités de recherche
                $unite = $this->uniteRechercheForm->getData();
                $this->getUniteRechercheService()->update($unite);

                $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' modifiée avec succès");

                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => "" . $uniteId], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");

            return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => "" . $uniteId], true);
        }

        $etablissements = $this->getEtablissementService()->getRepository()->findAll();
        $etablissementsRattachements = $this->getUniteRechercheService()->findEtablissementRattachement($unite);
        $domaineScientifiques = $this->getDomaineScientifiqueService()->getRepository()->findAll();

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form'                        => $this->uniteRechercheForm,
            'etablissements'              => $etablissements,
            'etablissementsRattachements' => $etablissementsRattachements,
            'domainesAssocies'            => $unite->getDomaines(),
            'domainesScientifiques'       => $domaineScientifiques,
            'logoContent'                 => $this->structureService->getLogoStructureContent($unite),
        ]);
        $viewModel->setTemplate('application/unite-recherche/modifier');

        return $viewModel;
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

            $this->uniteRechercheForm->setData($data);
            if ($this->uniteRechercheForm->isValid()) {
                /** @var UniteRecherche $unite */
                $unite = $this->uniteRechercheForm->getData();
                $this->getUniteRechercheService()->create($unite, $this->userContextService->getIdentityDb());

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoUniteRecherche($file['cheminLogo']['tmp_name'], $unite);
                }

                //creation automatique des roles associés à une unité de recherche
                $this->roleService->addRoleByStructure($unite);

                $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' créée avec succès");

                $uniteId = $unite->getStructure()->getId();

                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => $uniteId], true);
            }
        }

        $this->uniteRechercheForm->setAttribute('action', $this->url()->fromRoute('unite-recherche/ajouter'));

        $viewModel = new ViewModel([
            'form' => $this->uniteRechercheForm,
        ]);
        $viewModel->setTemplate('application/unite-recherche/modifier');

        return $viewModel;
    }

    public function supprimerAction()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);

        $this->getUniteRechercheService()->deleteSoftly($unite, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' supprimée avec succès");

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => $uniteId], true);
    }

    public function restaurerAction()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);

        $this->getUniteRechercheService()->undelete($unite);

        $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' restaurée avec succès");

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => $uniteId], true);
    }

    /**
     * @var UniteRechercheForm
     */
    private $uniteRechercheForm;

    /**
     * @param UniteRechercheForm $form
     * @return $this
     */
    public function setUniteRechercheForm(UniteRechercheForm $form)
    {
        $this->uniteRechercheForm = $form;

        return $this;
    }

    public function supprimerLogoAction()
    {
        $structureId = $this->params()->fromRoute("uniteRecherche");
        $this->supprimerLogoUniteRecherche();

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
    }

    public function supprimerLogoUniteRecherche()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite = $this->uniteRechercheService->getRepository()->findByStructureId($uniteId);

        $this->supprimerLogoStructure($unite);
    }

    /**
     * @param string         $cheminLogoUploade chemin vers le fichier temporaire associé au logo
     * @param UniteRecherche $unite
     */
    public function ajouterLogoUniteRecherche($cheminLogoUploade, UniteRecherche $unite = null)
    {
        if ($unite === null) {
            $uniteId = $this->params()->fromRoute("uniteRecherche");
            $unite = $this->uniteRechercheService->getRepository()->findByStructureId($uniteId);
        }

        $this->ajouterLogoStructure($unite, $cheminLogoUploade);
    }

    public function ajouterEtablissementRattachementAction()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        $etablissementId = $this->params()->fromRoute("etablissement");

        if ($etablissementId == 0) {
            $this->flashMessenger()->addErrorMessage("Pour ajouter un établissement de rattachement, veuillez sélectionner un établissement.");
        } else {
            $etablissement = $this->getEtablissementService()->getRepository()->find($etablissementId);
            if ($this->getUniteRechercheService()->existEtablissementRattachement($unite, $etablissement)) {
                $this->flashMessenger()->addErrorMessage("L'établissement de rattachement <strong>" . $etablissement->getLibelle() . "</strong> n'a pas pu être ajouter car déjà enregistré comme établissement de rattachement de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");
            } else {
                $this->getUniteRechercheService()->addEtablissementRattachement($unite, $etablissement);
                $this->flashMessenger()->addSuccessMessage("L'établissement <strong>" . $etablissement->getLibelle() . "</strong> vient d'être ajouter comme établissement de rattachement de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");
            }
        }

        $this->redirect()->toRoute("unite-recherche/modifier", [], [], true);
    }

    public function retirerEtablissementRattachementAction()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        $etablissementId = $this->params()->fromRoute("etablissement");
        $etablissement = $this->getEtablissementService()->getRepository()->find($etablissementId);

        $this->getUniteRechercheService()->removeEtablissementRattachement($unite, $etablissement);
        $this->flashMessenger()->addSuccessMessage("L'établissement <strong>" . $etablissement->getLibelle() . "</strong> n'est plus un établissement de rattachement de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");

        $this->redirect()->toRoute("unite-recherche/modifier", [], [], true);
    }


    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ajouterDomaineScientifiqueAction()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $domaineId = $this->params()->fromRoute("domaineScientifique");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        $domaine = $this->getDomaineScientifiqueService()->getRepository()->find($domaineId);

        if ($domaine !== null && !array_search($domaine, $unite->getDomaines())) {
            $domaine = $domaine->addUnite($unite);
            $unite = $unite->addDomaine($domaine);

            $this->getDomaineScientifiqueService()->updateDomaineScientifique($domaine);

            $this->flashMessenger()->addSuccessMessage("Le domaine scientifique <strong>" . $domaine->getLibelle() . "</strong> est maintenant un des domaines scientifiques de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");
        }
        $this->redirect()->toRoute("unite-recherche/modifier", [], [], true);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function retirerDomaineScientifiqueAction()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $domaineId = $this->params()->fromRoute("domaineScientifique");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        $domaine = $this->getDomaineScientifiqueService()->getRepository()->find($domaineId);

        $domaine = $domaine->removeUnite($unite);
        $unite = $unite->removeDomaine($domaine);

        $this->getDomaineScientifiqueService()->updateDomaineScientifique($domaine);

        $this->flashMessenger()->addSuccessMessage("Le domaine scientifique <strong>" . $domaine->getLibelle() . "</strong> ne fait plus parti des domaines scientifiques de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");

        return $this->redirect()->toRoute("unite-recherche/modifier", [], [], true);
    }
}