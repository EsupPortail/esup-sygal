<?php

namespace InscriptionAdministrative\Controller;

use Application\Controller\AbstractController;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceAwareTrait;

class InscriptionAdministrativeController extends AbstractController
{
    use InscriptionAdministrativeServiceAwareTrait;

    public function indexAction(): array
    {
        $qb = $this->inscriptionAdministrativeService->getRepository()->createQueryBuilder('ia')
            ->join('ia.source', 's')->addSelect('s')
            ->join('ia.doctorant', 'd')->addSelect('d')
            ->join('d.individu', 'di')->addSelect('di')
            ->join('ia.ecoleDoctorale', 'ed')->addSelect('ed')
            ->join('ed.structure', 'sed')->addSelect('sed')
            ->orderBy('ia.histoCreation', 'desc');
        $inscriptions = $qb->getQuery()->getArrayResult();

        return [
            'inscriptions' => $inscriptions,
        ];
    }

    public function voirAction(): array
    {
        $id = $this->params('id');
        $inscription = $this->inscriptionAdministrativeService->getRepository()->find($id);

        return [
            'inscription' => $inscription,
        ];
    }
}