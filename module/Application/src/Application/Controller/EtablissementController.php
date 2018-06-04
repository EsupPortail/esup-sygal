<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Role;
use Application\Entity\Db\SourceInterface;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Form\EtablissementForm;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Zend\View\Model\ViewModel;

/**
 * Class EtablissementController
 */
class EtablissementController extends AbstractController
{
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;
    use NotificationServiceAwareTrait;

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
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');
        $etablissements = $this->etablissementService->getEtablissements();
        usort($etablissements, function(Etablissement $a,Etablissement $b) {return $a->getLibelle() > $b->getLibelle();});

        $roles = null;
        $effectifs = null;
        if ($selected) {
            /**
             * @var Etablissement $etablissement
             * @var Role[] $roles
             */
            $etablissement  = $this->etablissementService->getEtablissementByStructureId($selected);
            $roles = $etablissement->getStructure()->getStructureDependantRoles();

            $effectifs = [];
            foreach ($roles as $role) {
                $individus = $this->individuService->getIndividuByRole($role);
                $effectifs[$role->getLibelle()] = $individus;
            }
        }

        $etablissementsSYGAL = $this->etablissementService->getEtablissementsBySource(SourceInterface::CODE_SYGAL);
        $etablissementsPrincipaux = array_filter($etablissementsSYGAL, function (Etablissement $etablissement) { return count($etablissement->getStructure()->getStructuresSubstituees())==0; });
        $etablissementsSecondaires = array_diff($etablissements, $etablissementsPrincipaux);

        /** retrait des structures substituées */
        //TODO faire cela dans le service ???
        $structuresSub = array_filter($etablissementsSYGAL, function (StructureConcreteInterface $structure) { return count($structure->getStructure()->getStructuresSubstituees())!=0; });
        $toRemove = [];
        foreach($structuresSub as $structure) {
            foreach ($structure->getStructure()->getStructuresSubstituees() as $sub) {
                $toRemove[] = $sub;
            }
        }
        $structures = [];
        foreach ($etablissementsSecondaires as $structure) {
            $found = false;
            foreach ($toRemove as $remove) {
                if($structure->getStructure()->getId() == $remove->getId()) $found = true;
            }
            if (!$found) $structures[] = $structure;
        }

        return new ViewModel([
            'structuresPrincipales'          => $etablissementsPrincipaux,
            'structuresSecondaires'          => $structures,
            'selected'                       => $selected,
            'roles'                          => $roles,
            'effectifs'                      => $effectifs,
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
                $this->etablissementService->create($etablissement, $this->userContextService->getIdentityDb());

                    // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoEtablissement($file['cheminLogo']['tmp_name'], $etablissement);
                }

                //creation automatique des roles associés à une unité de recherche
                $this->roleService->addRoleByStructure($etablissement);

                $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' créée avec succès");

                return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $etablissement->getStructure()->getId()]], true);
            }
        }

        $this->etablissementForm->setAttribute('action', $this->url()->fromRoute('etablissement/ajouter'));

        $viewModel = new ViewModel([
            'form' => $this->etablissementForm,
        ]);
        $viewModel->setTemplate('application/etablissement/modifier');

        return $viewModel;
    }

    public function supprimerAction()
    {
        $structureId = $this->params()->fromRoute("etablissement");
        $etablissement = $this->getEtablissementService()->getEtablissementByStructureId($structureId);

        $destructeur = $this->userContextService->getIdentityDb();
        $this->etablissementService->deleteSoftly($etablissement, $destructeur);
        $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' supprimé avec succès");

        return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId]], true);
    }

    public function modifierAction()
    {
        /** @var Etablissement $etablissement */
        $structureId = $this->params()->fromRoute("etablissement");
        $etablissement = $this->getEtablissementService()->getEtablissementByStructureId($structureId);
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
                return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId]], true);
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
                $this->etablissementService->update($etablissement);

                $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' modifiée avec succès");
                return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId]], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");
            return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId]], true);
        }

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form' => $this->etablissementForm,
        ]);
        $viewModel->setTemplate('application/etablissement/modifier');
        return $viewModel;

    }

    public function restaurerAction()
    {
        $structureId = $this->params()->fromRoute("etablissement");
        $etablissement = $this->getEtablissementService()->getEtablissementByStructureId($structureId);

        $this->etablissementService->undelete($etablissement);

        $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' restauré avec succès");

        return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $structureId]], true);
    }

    /**
     * Retire le logo associé à une établissement:
     * - modification base de donnée (champ CHEMIN_LOG <- null)
     * - effacement du fichier stocké sur le serveur
     */
    public function supprimerLogoEtablissement()
    {
        $structureId = $this->params()->fromRoute("etablissement");
        $etablissement = $this->getEtablissementService()->getEtablissementByStructureId($structureId);

        $this->etablissementService->deleteLogo($etablissement);
        $filename   = EtablissementController::getLogoFilename($etablissement, true);
        if (file_exists($filename)) {
            $result = unlink($filename);
            if ($result) {
                $this->flashMessenger()->addSuccessMessage("Le logo de l'école doctorale {$etablissement->getLibelle()} vient d'être supprimé.");
            } else {
                $this->flashMessenger()->addErrorMessage("Erreur lors de l'effacement du logo de l'établissement <strong>{$etablissement->getLibelle()}.</strong>");
            }
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour l'établissement <strong>{$etablissement->getLibelle()}.</strong>");
        }

    }

    /**
     * Ajoute le logo associé à une établissement:
     * - modification base de donnée (champ CHEMIN_LOG <- /public/Logos/Etab/LOGO_NAME)
     * - enregistrement du fichier sur le serveur
     * @param string $cheminLogoUploade     chemin vers le fichier temporaire associé au logo
     * @param Etablissement $etablissement
     */
    public function ajouterLogoEtablissement($cheminLogoUploade, Etablissement $etablissement = null)
    {
        if ($cheminLogoUploade === null || $cheminLogoUploade === '') {
            $this->flashMessenger()->addErrorMessage("Fichier logo invalide.");
            return;
        }

        if ($etablissement === null) {
            $structureId = $this->params()->fromRoute("etablissement");
            $etablissement = $this->getEtablissementService()->getEtablissementByStructureId($structureId);
        }
        $chemin         = EtablissementController::getLogoFilename($etablissement, false);
        $filename       = EtablissementController::getLogoFilename($etablissement, true);
        $result = rename($cheminLogoUploade, $filename);
        if ($result) {
            $this->flashMessenger()->addSuccessMessage("Le logo de l'établissement {$etablissement->getLibelle()} vient d'être ajouté.");
            $this->etablissementService->setLogo($etablissement,$chemin);
        } else {
            $this->flashMessenger()->addErrorMessage("Erreur lors de l'enregistrement du logo de l'établissement <strong>{$etablissement->getLibelle()}.</strong> ");
        }
    }

    /**
     * Retourne le chemin vers le logo d'une établissement
     * @param Etablissement $etablissement
     * @param bool $fullpath            si true chemin absolue sinon chemin relatif au répertoire de l'application
     * @return string                   le chemin vers le logo de l'établissement $etablissement
     */
    static public function getLogoFilename(Etablissement $etablissement, $fullpath=true)
    {
        $chemin = "";
//        if ($fullpath) $chemin .= APPLICATION_DIR;
        if ($fullpath) $chemin .= "/var/sygal-files";
        if ($etablissement->getCode()) $chemin .= "/ressources/Logos/Etab/".$etablissement->getCode().".png";
        else $chemin .= "/ressources/Logos/Etab/". uniqid().".png";
        return $chemin;
    }
}