<?php

namespace Application\Controller;

use Application\Entity\Db\TypeStructure;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class EcoleDoctoraleController extends StructureConcreteController
{
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
    public function informationAction()
    {
        $viewModel = parent::informationAction();

        $viewModel->setVariable('ecole', $viewModel->getVariable('structure'));

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