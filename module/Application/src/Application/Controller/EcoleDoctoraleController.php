<?php

namespace Application\Controller;

use Application\Controller\Traits\LogoAwareControllerTrait;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeStructure;
use Application\Form\EcoleDoctoraleForm;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\View\Model\ViewModel;

class EcoleDoctoraleController extends AbstractController
{
    use EcoleDoctoraleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;
    use FileServiceAwareTrait;
    use LogoAwareControllerTrait;

    public function indexAction()
    {
        $eds = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle');

        return new ViewModel([
            'ecoles' => $eds,
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
            'ecole'           => $ecole,
            'roleListing'     => $roleListings,
            'individuListing' => $individuListings,
            'logoContent'     => $this->structureService->getLogoStructureContent($ecole),
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
        $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);
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
            'logoContent' => $this->structureService->getLogoStructureContent($ecole),
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
        $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);

        $this->getEcoleDoctoraleService()->deleteSoftly($ecole, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' supprimée avec succès");

        return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecoleId], "fragment" => $ecoleId], true);
    }

    public function restaurerAction()
    {
        $ecoleId = $this->params()->fromRoute("ecoleDoctorale");
        $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);

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

    public function supprimerLogoEcoleDoctorale()
    {
        $ecoleId = $this->params()->fromRoute("ecoleDoctorale");
        $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);

        $this->supprimerLogoStructure($ecole);
    }

    /**
     * @param string         $cheminLogoUploade chemin vers le fichier temporaire associé au logo
     * @param EcoleDoctorale $ecole
     */
    public function ajouterLogoEcoleDoctorale($cheminLogoUploade, EcoleDoctorale $ecole = null)
    {
        if ($ecole === null) {
            $ecoleId = $this->params()->fromRoute("ecoleDoctorale");
            $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($ecoleId);
        }

        $this->ajouterLogoStructure($ecole, $cheminLogoUploade);
    }
}