<?php

namespace Structure\Controller;

use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\TypeStructure;
use These\Service\CoEncadrant\CoEncadrantServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class EcoleDoctoraleController extends StructureConcreteController
{
    use CoEncadrantServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;

    protected $codeTypeStructure = TypeStructure::CODE_ECOLE_DOCTORALE;

    /**
     * @var string
     */
    protected $routeName = 'ecole-doctorale';

    /**
     * @return EcoleDoctoraleService
     */
    protected function getStructureConcreteService()
    {
        return $this->ecoleDoctoraleService;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
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
        $viewModel = parent::informationAction();

        /** @var EcoleDoctorale $structureConcrete */
        $structureConcrete = $viewModel->getVariable('structure');
        $coencadrants = $this->getCoEncadrantService()->findCoEncadrantsByStructureConcrete($structureConcrete, false);
        $contenus = $this->getStructureDocumentService()->getContenusFichiers($structureConcrete->getStructure());

        $viewModel->setVariables([
            'ecole' => $viewModel->getVariable('structure'),
            'coencadrants' => $coencadrants,
            'contenus' => $contenus,
        ]);

        return $viewModel;
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