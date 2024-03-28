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
        $eds = null;
        if ($this->actualiteService->isSoutenance()) {
            $qb = $this->getEcoleDoctoraleService()->getRepository()->createQueryBuilder('ed')
                ->join('ed.structure', 's')
                ->andWhereNotHistorise('ed')
                ->andWhereNotHistorise('s')
                ->orderBy('s.sigle');
            $eds = $qb->getQuery()->getResult();
        }

        return new ViewModel([
            'ecoles' => $eds,
            'informations' => $this->informationService->getInformations(true),
        ]);
    }
}