<?php

namespace Application\Controller;

use Application\Service\Actualite\ActualiteServiceAwareTrait;
use Information\Service\InformationServiceAwareTrait;
use Laminas\View\Model\ViewModel;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;

class SoutenanceController extends AbstractController
{
    use ActualiteServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use InformationServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        return new ViewModel([
            'ecoles' => $this->actualiteService->isSoutenance() ? $this->getEcoleDoctoraleService()->getRepository()->findAll(): null,
            'informations' => $this->informationService->getInformations(true),
        ]);
    }
}