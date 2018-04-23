<?php

namespace Application\Controller;

use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\UniteRecherche;
use Application\Form\UniteRechercheForm;
use Application\RouteMatch;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use Zend\View\Model\ViewModel;

class UniteRechercheController extends AbstractController
{
    use UniteRechercheServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;
    use LdapPeopleServiceAwareTrait;
    use EtablissementServiceAwareTrait;

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
        if ($selected) {
            /**
             * @var StructureConcreteInterface $structure
             * @var Role[] $roles
             */
            $structure  = $this->uniteRechercheService->getUniteRechercheById($selected);
            $roles = $structure->getStructure()->getStructureDependantRoles();

            $effectifs = [];
            foreach ($roles as $role) {
                $individus = $this->individuService->getIndividuByRole($role);
                $effectifs[$role->getLibelle()] = $individus;
            }
        }

        $structuresAll = $this->uniteRechercheService->getUnitesRecherches();

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

        return new ViewModel([
            'structuresPrincipales'          => $structures,
            'selected'                       => $selected,
            'roles'                          => $roles,
            'effectifs'                      => $effectifs,
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
        $unite = $this->requestUniteRecherche();
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
                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
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
                $this->uniteRechercheService->update($unite);

                $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' modifiée avec succès");
                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");
            return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
        }

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form' => $this->uniteRechercheForm,
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
                $this->uniteRechercheService->create($unite, $this->userContextService->getIdentityDb());

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoUniteRecherche($file['cheminLogo']['tmp_name'], $unite);
                }

                //creation automatique des roles associés à une unité de recherche
                $this->roleService->addRoleByStructure($unite);

                $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' créée avec succès");

                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
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
        $unite = $this->requestUniteRecherche();

        $this->uniteRechercheService->deleteSoftly($unite, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' supprimée avec succès");

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
    }

    public function restaurerAction()
    {
        $unite = $this->requestUniteRecherche();

        $this->uniteRechercheService->undelete($unite);

        $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' restaurée avec succès");

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
    }

    /**
     * Ajout des individus et de leurs rôles dans la table INDIVIDU_ROLE
     * @return \Zend\Http\Response
     */
    public function ajouterIndividuAction()
    {
        $uniteId    = $this->params()->fromRoute('uniteRecherche');
        $data       = $this->params()->fromPost('individu');
        $roleId     = $this->params()->fromPost('role');

        if (!empty($data['id'])) {
            /** @var Individu $individu */
            $individu = $this->individuService->getRepository()->find($data['id']);
            if ($individu) {
                /**
                 * @var UniteRecherche $unite
                 * @var Role $role
                 * @var IndividuRole $individuRole
                 */
                $unite = $this->uniteRechercheService->getUniteRechercheById($uniteId);
                $role = $this->roleService->getRoleById($roleId);
                $individuRole = $this->roleService->addIndividuRole($individu,$role);

                $this->flashMessenger()->addSuccessMessage(
                    "<strong>{$individuRole->getIndividu()}</strong>". " est désormais " .
                    "<strong>{$individuRole->getRole()}</strong>". " de l'unité de recherche ".
                    "<strong>{$unite->getLibelle()}</strong>.");
            }
        }

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId]], true);
    }

    /**
     * Retrait des individus et de leurs rôles dans la table INDIVIDU_ROLE
     * @return \Zend\Http\Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function retirerIndividuAction()
    {
        $urId = $this->params()->fromRoute('uniteRecherche');
        $irId = $this->params()->fromRoute('edi');

        $unite = null;
        if ($urId !== null) {
            $unite = $this->uniteRechercheService->getUniteRechercheById($urId);
        }

        if ($irId) {
            $individuRole = $this->roleService->removeIndividuRoleById($irId);

            $this->flashMessenger()->addSuccessMessage(
                 "<strong>{$individuRole->getIndividu()}</strong>" . " n'est plus n'est plus "
                ."<strong>{$individuRole->getRole()}</strong>" . " de l'unite de recherche "
                ."<strong>{$unite->getLibelle()}</strong>"."</strong>");

            return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $urId]], true);
        }

        return $this->redirect()->toRoute('unite-recherche', [], [], true);
    }

    /**
     * @return UniteRecherche
     */
    private function requestUniteRecherche()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getUniteRecherche();
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

    /**
     * Retire le logo associé à une unite de recherche:
     * - modification base de donnée (champ CHEMIN_LOG <- null)
     * - effacement du fichier stocké sur le serveur
     */
    public function supprimerLogoUniteRecherche()
    {
        $unite = $this->requestUniteRecherche();

        $this->uniteRechercheService->deleteLogo($unite);
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

        if ($unite === null) $unite      = $this->requestUniteRecherche();
        $chemin     = UniteRechercheController::getLogoFilename($unite, false);
        $filename   = UniteRechercheController::getLogoFilename($unite, true);
        $result = rename($cheminLogoUploade, $filename);
        if ($result) {
            $this->flashMessenger()->addSuccessMessage("Le logo de l'unité de recherche {$unite->getLibelle()} vient d'être ajouté.");
            $this->uniteRechercheService->setLogo($unite,$chemin);
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
//        if ($fullpath) $chemin .= APPLICATION_DIR;
        if ($fullpath) $chemin .= "/var/sygal-files";
        $chemin .= "/ressources/Logos/UR/".$unite->getSourceCode()."-".$unite->getSigle().".png";
        return $chemin;
    }
}