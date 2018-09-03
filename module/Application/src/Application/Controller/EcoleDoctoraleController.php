<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Form\EcoleDoctoraleForm;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\View\Model\ViewModel;

class EcoleDoctoraleController extends AbstractController
{
    use EcoleDoctoraleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    /**
     * L'index récupére :
     * - la liste des écoles doctorales
     * - l'école doctorale sélectionnée
     * - la liste des rôles associées à l'école
     * - un tableau de tableaux des rôles associés à chaque rôle
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $structuresAll = $this->getEcoleDoctoraleService()->getRepository()->findAll();

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
            'ecoles'                         => $structures,
        ]);
    }

    public function informationAction()
    {
        $id = $this->params()->fromRoute('ecoleDoctorale');
        $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($id);
        if ($ecole === null) {
            throw new RuntimeException("Aucune école doctorale ne possède l'identifiant renseigné.");
        }

        $roleListings = [];
        $individuListings = [];
        $roles = $this->getRoleService()->getRolesByStructure($ecole->getStructure());
        $individus = $this->getRoleService()->getIndividuByStructure($ecole->getStructure());
        $individuRoles = $this->getRoleService()->getIndividuRoleByStructure($ecole->getStructure());

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

        return new ViewModel([
            'ecole' => $ecole,
            'roleListing' => $roleListings,
            'individuListing' => $individuListings,
        ]);

    }

    /**
     * Modifier permet soit d'afficher le formulaire associé à la modification soit de mettre à jour
     * les données associées à une école doctorale (Sigle, Libellé et Logo)
     *
     * @return \Zend\Http\Response|ViewModel
     *
     * TODO en cas de changement de SIGLE penser à faire un renommage du logo
     */
    public function modifierAction()
    {
        /** @var EcoleDoctorale $ecole */
        $ecoleId = $this->params()->fromRoute("ecoleDoctorale");
        $ecole  = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);
        $this->ecoleDoctoraleForm->bind($ecole);

        // si POST alors on revient du formulaire
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            // action d'affacement du logo
            if (isset($data['supprimer-logo'])) {
                $this->supprimerLogoEcoleDoctorale();
                return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecoleId], "fragment" => $ecoleId], true);
            }

            // action de modification
            $this->ecoleDoctoraleForm->setData($data);
            if ($this->ecoleDoctoraleForm->isValid()) {

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoEcoleDoctorale($file['cheminLogo']['tmp_name']);
                }
                // mise à jour des données relatives aux écoles doctorales
                $ecole = $this->ecoleDoctoraleForm->getData();
                $this->getEcoleDoctoraleService()->update($ecole);

                $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' modifiée avec succès");
                return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecoleId], "fragment" => $ecoleId], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");
            return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecoleId], "fragment" => $ecoleId], true);
        }

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form' => $this->ecoleDoctoraleForm,
        ]);
        $viewModel->setTemplate('application/ecole-doctorale/modifier');
        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ajouterAction()
    {
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            $this->ecoleDoctoraleForm->setData($data);
            if ($this->ecoleDoctoraleForm->isValid()) {
                /** @var EcoleDoctorale $ecole */
                $ecole = $this->ecoleDoctoraleForm->getData();
                $ecole = $this->getEcoleDoctoraleService()->create($ecole, $this->userContextService->getIdentityDb());

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoEcoleDoctorale($file['cheminLogo']['tmp_name'], $ecole);
                }

                //creation automatique des roles associés à une unité de recherche
                $this->roleService->addRoleByStructure($ecole);

                $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' créée avec succès");

                $ecoleId = $ecole->getStructure()->getId();
                return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecoleId], "fragment" => $ecoleId], true);
            }
        }

        $this->ecoleDoctoraleForm->setAttribute('action', $this->url()->fromRoute('ecole-doctorale/ajouter'));

        $viewModel = new ViewModel([
            'form' => $this->ecoleDoctoraleForm,
        ]);
        $viewModel->setTemplate('application/ecole-doctorale/modifier');

        return $viewModel;
    }

    public function supprimerAction()
    {
        $ecoleId = $this->params()->fromRoute("ecoleDoctorale");
        $ecole  = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);

        $this->getEcoleDoctoraleService()->deleteSoftly($ecole, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' supprimée avec succès");

        return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecoleId], "fragment" => $ecoleId], true);
    }

    public function restaurerAction()
    {
        $ecoleId = $this->params()->fromRoute("ecoleDoctorale");
        $ecole  = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);

        $this->getEcoleDoctoraleService()->undelete($ecole);

        $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' restaurée avec succès");

        return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecoleId], "fragment" => $ecoleId], true);
    }

    /**
     * @var EcoleDoctoraleForm
     */
    private $ecoleDoctoraleForm;

    /**
     * @param EcoleDoctoraleForm $form
     * @return $this
     */
    public function setEcoleDoctoraleForm(EcoleDoctoraleForm $form)
    {
        $this->ecoleDoctoraleForm = $form;

        return $this;
    }

    public function supprimerLogoAction()
    {
        $structureId = $this->params()->fromRoute("ecoleDoctorale");
        $this->supprimerLogoEcoleDoctorale();
        return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $structureId], "fragment" => $structureId], true);
    }

    /**
     * Retire le logo associé à une école doctorale:
     * - modification base de donnée (champ CHEMIN_LOG <- null)
     * - effacement du fichier stocké sur le serveur
     */
    public function supprimerLogoEcoleDoctorale()
    {
        $ecoleId = $this->params()->fromRoute("ecoleDoctorale");
        $ecole  = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);

        $this->getEcoleDoctoraleService()->deleteLogo($ecole);
        $filename   = EcoleDoctoraleController::getLogoFilename($ecole, true);
        if (file_exists($filename)) {
            $result = unlink($filename);
            if ($result) {
                $this->flashMessenger()->addSuccessMessage("Le logo de l'école doctorale {$ecole->getLibelle()} vient d'être supprimé.");
            } else {
                $this->flashMessenger()->addErrorMessage("Erreur lors de l'effacement du logo de l'école doctorale <strong>{$ecole->getLibelle()}.</strong>");
            }
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour l'école doctorale <strong>{$ecole->getLibelle()}.</strong>");
        }

    }

    /**
     * Ajoute le logo associé à une école doctorale:
     * - modification base de donnée (champ CHEMIN_LOG <- /public/Logos/ED/LOGO_NAME)
     * - enregistrement du fichier sur le serveur
     * @param string $cheminLogoUploade     chemin vers le fichier temporaire associé au logo
     * @param EcoleDoctorale $ecole
     */
    public function ajouterLogoEcoleDoctorale($cheminLogoUploade, EcoleDoctorale $ecole = null)
    {
        if ($cheminLogoUploade === null || $cheminLogoUploade === '') {
            $this->flashMessenger()->addErrorMessage("Fichier logo invalide.");
            return;
        }

        if ($ecole === null) {
            $ecoleId = $this->params()->fromRoute("ecoleDoctorale");
            $ecole  = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);
        }
        $chemin     = EcoleDoctoraleController::getLogoFilename($ecole, false);
        $filename   = EcoleDoctoraleController::getLogoFilename($ecole, true);
        $result = rename($cheminLogoUploade, $filename);
        if ($result) {
            $this->flashMessenger()->addSuccessMessage("Le logo de l'école doctorale {$ecole->getLibelle()} vient d'être ajouté.");
            $this->getEcoleDoctoraleService()->setLogo($ecole,$chemin);
        } else {
            $this->flashMessenger()->addErrorMessage("Erreur lors de l'enregistrement du logo de l'école doctorale <strong>{$ecole->getLibelle()}.</strong>");
        }
    }

    /**
     * Retourne le chemin vers le logo d'une école doctorale
     * @param EcoleDoctorale $ecole
     * @param bool $fullpath            si true chemin absolue sinon chemin relatif au répertoire de l'application
     * @return string                   le chemin vers le logo de l'école doctorale $ecole
     */
    static public function getLogoFilename(EcoleDoctorale $ecole, $fullpath=true)
    {
        $chemin = "";
        if ($fullpath) $chemin .= Structure::PATH;
        $chemin .= "/ressources/Logos/ED/".$ecole->getSourceCode()."-".$ecole->getSigle().".png";
        return $chemin;
    }
}