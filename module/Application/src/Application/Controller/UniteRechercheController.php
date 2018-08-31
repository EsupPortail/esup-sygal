<?php

namespace Application\Controller;

use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\UniteRecherche;
use Application\Form\UniteRechercheForm;
use Application\Service\DomaineScientifiqueServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Zend\View\Model\ViewModel;

class UniteRechercheController extends AbstractController
{
    use UniteRechercheServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use DomaineScientifiqueServiceAwareTrait;

    /**
     * L'index récupére :
     * - la liste des unités de recherches
     * - l'unité sélectionnée
     * - la liste des rôles associées à l'unité
     * - un tableau de tableaux des rôles associés à chaque rôle
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');

        $roles = null;
        $effectifs = null;
        $structure = null;
        if ($selected) {
            /**
             * @var StructureConcreteInterface $structure
             * @var Role[] $roles
             */
            $selectedStructure  = $this->getUniteRechercheService()->getRepository()->findByStructureId($selected);
            $roles = $selectedStructure->getStructure()->getStructureDependantRoles();

            $effectifs = [];
            foreach ($roles as $role) {
                $individus = $this->individuService->getRepository()->findByRole($role);
                $effectifs[$role->getLibelle()] = $individus;
            }
        }

        $structuresAll = $this->getUniteRechercheService()->getRepository()->findAll();

        /** retrait des structures substituées */
        //TODO faire cela dans le service ???
        $structuresSub = array_filter($structuresAll, function (StructureConcreteInterface $structure) { return count($structure->getStructure()->getStructuresSubstituees())!=0; });
        $toRemove = [];
        foreach($structuresSub as $structure) {
            foreach ($structure->getStructure()->getStructuresSubstituees() as $sub) {
                $toRemove[] = $sub;
            }
        }
        $structures = [];
        foreach ($structuresAll as $structure) {
            $found = false;
            foreach ($toRemove as $remove) {
                if($structure->getStructure()->getId() == $remove->getId()) $found = true;
            }
            if (!$found) $structures[] = $structure;
        }
        $rattachements = null;
        if ($selectedStructure !== null) $rattachements = $this->getUniteRechercheService()->findEtablissementRattachement($selectedStructure);
        $domaines = null;
        if ($selectedStructure !== null) $domaines = $selectedStructure->getDomaines();

        return new ViewModel([
            'structuresPrincipales'          => $structures,
            'selected'                       => $selected,
            'roles'                          => $roles,
            'effectifs'                      => $effectifs,
            'rattachements'                  => $rattachements,
            'domaines'                       => $domaines,
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
        $unite  = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
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
                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => "".$uniteId], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");
            return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => "".$uniteId], true);
        }

        $etablissements = $this->getEtablissementService()->getRepository()->findAll();
        $etablissementsRattachements = $this->getUniteRechercheService()->findEtablissementRattachement($unite);
        $domaineScientifiques = $this->getDomaineScientifiqueService()->getRepository()->findAll();

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form' => $this->uniteRechercheForm,
            'etablissements' => $etablissements,
            'etablissementsRattachements' => $etablissementsRattachements,
            'domainesAssocies' => $unite->getDomaines(),
            'domainesScientifiques' => $domaineScientifiques,
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
        $unite  = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);

        $this->getUniteRechercheService()->deleteSoftly($unite, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' supprimée avec succès");

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId], "fragment" => $uniteId], true);
    }

    public function restaurerAction()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite  = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);

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

    /**
     * Retire le logo associé à une unite de recherche:
     * - modification base de donnée (champ CHEMIN_LOG <- null)
     * - effacement du fichier stocké sur le serveur
     */
    public function supprimerLogoUniteRecherche()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite  = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);

        $this->getUniteRechercheService()->deleteLogo($unite);
        $filename   = UniteRechercheController::getLogoFilename($unite, true);
        if (file_exists($filename)) {
            $result = unlink($filename);
            if ($result) {
                $this->flashMessenger()->addSuccessMessage("Le logo de l'unité de recherche {$unite->getLibelle()} vient d'être supprimé.");
            } else {
                $this->flashMessenger()->addErrorMessage("Erreur lors de l'effacement du logo de l'unité de recherche <strong>{$unite->getLibelle()}.</strong>");
            }
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour l'unité de recherche <strong>{$unite->getLibelle()}.</strong>");
        }

    }

    /**
     * Ajoute le logo associé à une unité de recherche:
     * - modification base de donnée (champ CHEMIN_LOG <- /public/Logos/UR/LOGO_NAME)
     * - enregistrement du fichier sur le serveur
     * @param string $cheminLogoUploade     chemin vers le fichier temporaire associé au logo
     * @param UniteRecherche $unite
     */
    public function ajouterLogoUniteRecherche($cheminLogoUploade, UniteRecherche $unite = null)
    {
        if ($cheminLogoUploade === null || $cheminLogoUploade === '') {
            $this->flashMessenger()->addErrorMessage("Fichier logo invalide.");
            return;
        }

        if ($unite === null) {
            $uniteId = $this->params()->fromRoute("uniteRecherche");
            $unite  = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        }
        $chemin     = UniteRechercheController::getLogoFilename($unite, false);
        $filename   = UniteRechercheController::getLogoFilename($unite, true);
        $result = rename($cheminLogoUploade, $filename);
        if ($result) {
            $this->flashMessenger()->addSuccessMessage("Le logo de l'unité de recherche {$unite->getLibelle()} vient d'être ajouté.");
            $this->getUniteRechercheService()->setLogo($unite,$chemin);
        } else {
            $this->flashMessenger()->addErrorMessage("Erreur lors de l'enregistrement du logo de l'unité de recherche <strong>{$unite->getLibelle()}</strong>.");
        }
    }

    /**
     * Retourne le chemin vers le logo d'une unité de recherche
     * @param UniteRecherche $unite
     * @param bool $fullpath            si true chemin absolue sinon chemin relatif au répertoire de l'application
     * @return string                   le chemin vers le logo de l'unité de recherche $ecole
     */
    static public function getLogoFilename(UniteRecherche $unite, $fullpath=true)
    {
        $chemin = "";
        if ($fullpath) $chemin .= Structure::PATH;
        $chemin .= "/ressources/Logos/UR/".$unite->getSourceCode()."-".$unite->getSigle().".png";
        return $chemin;
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

        $this->redirect()->toRoute("unite-recherche/modifier",[],[], true);
    }

    public function retirerEtablissementRattachementAction()
    {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        $etablissementId = $this->params()->fromRoute("etablissement");
        $etablissement = $this->getEtablissementService()->getRepository()->find($etablissementId);

        $this->getUniteRechercheService()->removeEtablissementRattachement($unite, $etablissement);
        $this->flashMessenger()->addSuccessMessage("L'établissement <strong>".$etablissement->getLibelle()."</strong> n'est plus un établissement de rattachement de l'unité de recherche <strong>".$unite->getLibelle()."</strong>.");

        $this->redirect()->toRoute("unite-recherche/modifier",[],[], true);
    }


    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ajouterDomaineScientifiqueAction() {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $domaineId = $this->params()->fromRoute("domaineScientifique");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        $domaine = $this->getDomaineScientifiqueService()->getRepository()->find($domaineId);

        if ($domaine !== null && !array_search($domaine, $unite->getDomaines())) {
            $domaine = $domaine->addUnite($unite);
            $unite = $unite->addDomaine($domaine);

            $this->getDomaineScientifiqueService()->updateDomaineScientifique($domaine);

            $this->flashMessenger()->addSuccessMessage("Le domaine scientifique <strong>".$domaine->getLibelle()."</strong> est maintenant un des domaines scientifiques de l'unité de recherche <strong>".$unite->getLibelle()."</strong>.");
        }
        $this->redirect()->toRoute("unite-recherche/modifier",[],[], true);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function retirerDomaineScientifiqueAction() {
        $uniteId = $this->params()->fromRoute("uniteRecherche");
        $domaineId = $this->params()->fromRoute("domaineScientifique");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($uniteId);
        $domaine = $this->getDomaineScientifiqueService()->getRepository()->find($domaineId);

        $domaine = $domaine->removeUnite($unite);
        $unite = $unite->removeDomaine($domaine);

        $this->getDomaineScientifiqueService()->updateDomaineScientifique($domaine);

        $this->flashMessenger()->addSuccessMessage("Le domaine scientifique <strong>".$domaine->getLibelle()."</strong> ne fait plus parti des domaines scientifiques de l'unité de recherche <strong>".$unite->getLibelle()."</strong>.");

        return $this->redirect()->toRoute("unite-recherche/modifier",[],[], true);
    }
}