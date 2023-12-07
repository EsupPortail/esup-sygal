<?php

namespace Structure\Controller;

use InvalidArgumentException;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use These\Service\CoEncadrant\CoEncadrantServiceAwareTrait;

class EcoleDoctoraleController extends StructureConcreteController
{
    use CoEncadrantServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;

    protected $codeTypeStructure = TypeStructure::CODE_ECOLE_DOCTORALE;

    protected string $routeName = 'ecole-doctorale';
    protected string $routeParamName = 'ecole-doctorale';

    /**
     * @return EcoleDoctoraleService
     */
    protected function getStructureConcreteService()
    {
        return $this->ecoleDoctoraleService;
    }

    public function indexAction(): ViewModel
    {
        $vm = parent::indexAction();

        return new ViewModel([
            'ecoles' => $vm->getVariable('structures'),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function voirAction(): ViewModel
    {
        $id = $this->params()->fromRoute('ecole-doctorale');

        /** @var EcoleDoctorale $structureConcrete */
        $structureConcrete = $this->ecoleDoctoraleService->getRepository()->find($id);
        if ($structureConcrete === null) {
            throw new InvalidArgumentException("Ecole doctorale introuvable avec cet id");
        }

        $vars = $this->loadInformationForStructure($structureConcrete);

        return (new ViewModel($vars))
            ->setTemplate('structure/ecole-doctorale/information');
    }

    protected function loadInformationForStructure(StructureConcreteInterface $structureConcrete): array
    {
        $vars = parent::loadInformationForStructure($structureConcrete);

        /** @var EcoleDoctorale $structureConcrete */
        $coencadrants = $this->getCoEncadrantService()->findCoEncadrantsByStructureConcrete($structureConcrete, false);
        $contenus = $this->getStructureDocumentService()->getContenusFichiers($structureConcrete->getStructure());

        return array_merge($vars, [
            'ecole' => $structureConcrete,
            'coencadrants' => $coencadrants,
            'contenus' => $contenus,
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

        $viewModel->setTemplate('structure/ecole-doctorale/modifier');

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

        $viewModel->setTemplate('structure/ecole-doctorale/modifier');

        return $viewModel;
    }

    public function rechercherAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $unites = $this->getEcoleDoctoraleService()->getRepository()->findByText($term);
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