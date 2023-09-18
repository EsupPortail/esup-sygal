<?php

namespace Structure\Controller;

use Structure\Entity\Db\Etablissement;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Laminas\View\Model\JsonModel;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

/**
 * Class EtablissementController
 */
class EtablissementController extends StructureConcreteController
{
    use EtablissementServiceAwareTrait;
    use RoleServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

    protected $codeTypeStructure = TypeStructure::CODE_ETABLISSEMENT;

    /**
     * @var string
     */
    protected $routeName = 'etablissement';

    /**
     * @return EtablissementService
     */
    protected function getStructureConcreteService()
    {
        return $this->etablissementService;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $etablissements = $this->structureService->findAllStructuresAffichablesByType($this->codeTypeStructure, 'libelle');

        $etablissementsPrincipaux = array_filter($etablissements, function (Etablissement $e) {
            return $e->estMembre();
        });
        $etablissementsExternes = array_filter($etablissements, function (Etablissement $e) {
            return !$e->estMembre();
        });
        $etablissementsCeds = array_filter($etablissements, function (Etablissement $e) {
            return $e->estCed();
        });

        if (count($etablissementsCeds) > 1) {
            throw new \RuntimeException("Anomalie rencontrée : il existe plusieurs établissements CED");
        }

        return new ViewModel([
            'etablissementsSygal'    => $etablissementsPrincipaux,
            'etablissementsExternes' => $etablissementsExternes,
            'etablissementsCeds' => $etablissementsCeds,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function voirAction(): ViewModel
    {
        $id = $this->params()->fromRoute('etablissement');

        /** @var Etablissement $structureConcrete */
        $structureConcrete = $this->etablissementService->getRepository()->find($id);

        return $this->forward()->dispatch(self::class, [
            'action' => 'information',
            'structure' => $structureConcrete->getStructure(false)->getId(),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function informationAction(): ViewModel
    {
        $id = $this->params()->fromRoute('structure');
        /** @var Etablissement $etablissement */
        $etablissement = $this->getStructureConcreteService()->getRepository()->findByStructureId($id);
        if ($etablissement === null) {
            throw new RuntimeException("Aucun établissement ne possède l'identifiant renseigné.");
        }
        $contenus = $this->getStructureDocumentService()->getContenusFichiers($etablissement->getStructure());

        $roleListings = [];
        $individuListings = [];
        $roles = $this->roleService->findRolesForStructure($etablissement->getStructure());
        $individus = $this->roleService->findIndividuForStructure($etablissement->getStructure());
        $individuRoles = $this->roleService->findIndividuRoleByStructure($etablissement->getStructure());

        /** @var Role $role */
        foreach ($roles as $role) {
            if (!$role->isTheseDependant()) {
                $roleListings [$role->getLibelle()] = 0;
            }
        }

        /** @var Individu $individu */
        foreach ($individus as $individu) {
            $denomination = $individu->getNomComplet(false, false, false, true);
            $individuListings[$denomination] = [];
        }

        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            if (!$individuRole->getRole()->isTheseDependant()) {
                $denomination = $individuRole->getIndividu()->getNomComplet(false, false, false, true);
                $role = $individuRole->getRole()->getLibelle();
                $individuListings[$denomination][] = $role;
                $roleListings[$role]++;
            }
        }

        return new ViewModel([
            'etablissement'   => $etablissement,
            'roleListing'     => $roleListings,
            'individuListing' => $individuListings,
            'logoContent'     => $this->structureService->getLogoStructureContent($etablissement->getStructure()),
            'contenus'        => $contenus,
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function modifierAction()
    {
        $viewModel = parent::modifierAction();

        if ($viewModel instanceof Response) {
            return $viewModel;
        }

        $viewModel->setTemplate('structure/etablissement/modifier');

        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     */
    public function ajouterAction()
    {
        $viewModel = parent::ajouterAction();

        if ($viewModel instanceof Response) {
            return $viewModel;
        }

        $viewModel->setTemplate('structure/etablissement/modifier');

        return $viewModel;
    }

    public function rechercherAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $unites = $this->getEtablissementService()->getRepository()->findByText($term);
            $result = [];
            foreach ($unites as $unite) {
                $result[] = array(
                    'id' => $unite->getId(),            // identifiant unique de l'item
                    'label' => $unite->getStructure()->getLibelle(),    // libellé de l'item
                    'extra' => $unite->getStructure()->getSigle(),      // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }

}