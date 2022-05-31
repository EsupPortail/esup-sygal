<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\TypeStructure;
use Application\Service\CoEncadrant\CoEncadrantServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Laminas\Http\Response;
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
    public function informationAction(): ViewModel
    {
        $viewModel = parent::informationAction();

        /** @var EcoleDoctorale $structureConcrete */
        $structureConcrete = $viewModel->getVariable('structure');
        $coencadrants = $this->getCoEncadrantService()->getCoEncadrantsByStructureConcrete($structureConcrete, false);
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

        $viewModel->setTemplate('application/ecole-doctorale/modifier');

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

        $viewModel->setTemplate('application/ecole-doctorale/modifier');

        return $viewModel;
    }
}