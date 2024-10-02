<?php

namespace Structure\Controller;

use Application\Service\Role\RoleServiceAwareTrait;
use InvalidArgumentException;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;

/**
 * @property \Structure\Form\EtablissementForm $structureForm
 */
class EtablissementController extends StructureConcreteController
{
    use EtablissementServiceAwareTrait;
    use RoleServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

    protected $codeTypeStructure = TypeStructure::CODE_ETABLISSEMENT;

    protected string $routeName = 'etablissement';
    protected string $routeParamName = 'etablissement';

    /**
     * @return EtablissementService
     */
    protected function getStructureConcreteService()
    {
        return $this->etablissementService;
    }

    public function indexAction(): ViewModel
    {
        $viewModel = parent::indexAction();

        $etablissements = $viewModel->getVariable('structures');

        $etablissementsMembres = array_filter($etablissements, fn(Etablissement $e) =>$e->estMembre());
        $etablissementsCeds = array_filter($etablissements, fn(Etablissement $e) => $e->estCed());
        $etablissementsAutres = array_filter($etablissements, fn(Etablissement $e) => !$e->estMembre() && !$e->estCed());

        if (count($etablissementsCeds) > 1) {
            throw new \RuntimeException("Anomalie rencontrée : il existe plusieurs établissements CED");
        }

        return new ViewModel([
            'etablissementsMembres' => $etablissementsMembres,
            'etablissementsCeds' => $etablissementsCeds,
            'etablissementsAutres' => $etablissementsAutres,
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
        if ($structureConcrete === null) {
            throw new InvalidArgumentException("Etablissement introuvable avec cet id");
        }

        $vars = $this->loadInformationForStructure($structureConcrete);

        return (new ViewModel($vars))
            ->setTemplate('structure/etablissement/information');
    }

    protected function loadInformationForStructure(StructureConcreteInterface $structureConcrete): array
    {
        $vars = parent::loadInformationForStructure($structureConcrete);

        $contenus = $this->getStructureDocumentService()->getContenusFichiers($structureConcrete->getStructure());

        $roleListings = [];
        $individuListings = [];
        $roles = $this->roleService->findRolesForStructure($structureConcrete->getStructure());
        $individus = $this->roleService->findIndividuForStructure($structureConcrete->getStructure());
        $individuRoles = $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure());

        foreach ($roles as $role) {
            if (!$role->isTheseDependant()) {
                $roleListings [$role->getLibelle()] = 0;
            }
        }

        foreach ($individus as $individu) {
            $denomination = $individu->getNomComplet(false, false, false, true);
            $individuListings[$denomination] = [];
        }

        foreach ($individuRoles as $individuRole) {
            if (!$individuRole->getRole()->isTheseDependant()) {
                $denomination = $individuRole->getIndividu()->getNomComplet(false, false, false, true);
                $role = $individuRole->getRole()->getLibelle();
                $individuListings[$denomination][] = $role;
                $roleListings[$role]++;
            }
        }

        return array_merge($vars, [
            'etablissement'   => $structureConcrete,
            'roleListing'     => $roleListings,
            'individuListing' => $individuListings,
            'logoContent'     => $this->structureService->getLogoStructureContent($structureConcrete->getStructure()),
            'contenus'        => $contenus,
        ]);
    }

    public function ajouterAction(): Response|ViewModel
    {
        $type = $this->params('type', Etablissement::TYPE_AUTRE);

        $etablissement = new Etablissement();
        $etablissement->initializeForType($type);
        $this->structureForm->bind($etablissement);

        $viewModel = parent::ajouterAction();

        if ($viewModel instanceof Response) {
            return $viewModel;
        }

        $viewModel->setTemplate('structure/etablissement/modifier');

        return $viewModel;
    }

    public function modifierAction(): Response|ViewModel
    {
        /** @var Etablissement $structureConcrete */
        $structureConcrete = $this->getRequestedStructureConcrete();
        $structureConcrete->initializeForType();

        $viewModel = parent::modifierAction();

        if ($viewModel instanceof Response) {
            return $viewModel;
        }

        $viewModel->setTemplate('structure/etablissement/modifier');

        return $viewModel;
    }

    public function rechercherAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $etabs = $this->getEtablissementService()->getRepository()->findByText($term);
            $result = [];
            foreach ($etabs as $etab) {
                $label = $etab['structure']['libelle'];
                if ($etab['structure']['sigle']) {
                    $label .= sprintf(' (%s)', $etab['structure']['sigle']);
                }
                $result[] = [
                    /** Attention à être cohérent avec {@see Etablissement::createSearchFilterValueOption() } */
                    'id' => $etab['id'], // identifiant unique de l'item
                    'label' => $label, // libellé de l'item
                    'extra' => $etab['structure']['estFermee'] ? 'Fermé' : null, // infos complémentaires (facultatives) sur l'item
                ];
            }
            usort($result, fn($a, $b) => $a['label'] <=> $b['label']);

            return new JsonModel($result);
        }
        exit;
    }

}