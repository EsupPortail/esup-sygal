<?php

namespace Application\Controller;

use Application\Service\Actualite\ActualiteServiceAwareTrait;
use Information\Service\InformationServiceAwareTrait;
use Laminas\View\Model\ViewModel;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;

class OffreController extends AbstractController
{
    use ActualiteServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use InformationServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        return new ViewModel([
            'offre' => $this->actualiteService->isOffre() ? $this->getEcoleDoctoraleService()->getOffre() : null,
            'informations' => $this->informationService->getInformations(true),
        ]);
    }
}