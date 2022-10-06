<?php

namespace Application\Controller;

use Application\Service\Actualite\ActualiteServiceAwareTrait;
use Information\Service\InformationServiceAwareTrait;
use Laminas\View\Model\ViewModel;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;

class ActualiteController extends AbstractController
{
    use ActualiteServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use InformationServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        return new ViewModel([
            'url' => $this->actualiteService->isActif() ? $this->actualiteService->getUrl() : null,
            'informations' => $this->informationService->getInformations(true),
        ]);
    }
}