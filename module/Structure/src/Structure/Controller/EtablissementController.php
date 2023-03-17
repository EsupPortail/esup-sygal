<?php

namespace Structure\Controller;

use Application\Entity\Db\Role;
use Application\Service\Variable\VariableServiceAwareTrait;
use InvalidArgumentException;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Laminas\View\Model\JsonModel;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Util;

/**
 * Class EtablissementController
 */
class EtablissementController extends StructureConcreteController
{
    use EtablissementServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;
    use VariableServiceAwareTrait;

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

        $etablissementsInscrs = array_filter($etablissements, fn(Etablissement $e) =>$e->estInscription());
        $etablissementsCeds = array_filter($etablissements, fn(Etablissement $e) => $e->estCed());
        $etablissementsAutres = array_filter($etablissements, fn(Etablissement $e) => !$e->estInscription() && !$e->estCed());

        if (count($etablissementsCeds) > 1) {
            throw new \RuntimeException("Anomalie rencontrée : il existe plusieurs établissements CED");
        }

        return new ViewModel([
            'etablissementsInscrs' => $etablissementsInscrs,
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

    /**
     * @param Etablissement $structureConcrete
     */
    protected function loadInformationForStructure(StructureConcreteInterface $structureConcrete): array
    {
        $vars = parent::loadInformationForStructure($structureConcrete);

        $contenusFichiers = $this->getStructureDocumentService()->getContenusFichiers($structureConcrete->getStructure());

        $roleListings = [];
        $individuListings = [];
        $roles = $this->applicationRoleService->findRolesForStructure($structureConcrete->getStructure());
        $individus = $this->applicationRoleService->findIndividuForStructure($structureConcrete->getStructure());
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure($structureConcrete->getStructure());

        $variables = $structureConcrete->estInscription() ? $this->variableService->getRepository()->findAllByEtab($structureConcrete) : [];

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
            'contenusFichiers' => $contenusFichiers,
            'variables' => $variables
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
                    'text' => $label, // pour Select2.js
                    'extra' => $etab['structure']['estFermee'] ? 'Fermé' : null, // infos complémentaires (facultatives) sur l'item
                ];
            }
            usort($result, fn($a, $b) => $a['label'] <=> $b['label']);

            return new JsonModel($result);
        }
        exit;
    }

}