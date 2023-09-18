<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Substitution\Service\DoublonServiceAwareTrait;
use Substitution\Service\SubstitutionServiceAwareTrait;

/**
 * @method FlashMessenger flashMessenger()
 */
class IndexController extends AbstractActionController
{
    use DoublonServiceAwareTrait;
    use SubstitutionServiceAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function accueilAction(): array
    {
        return [
            'substitutionsCount' => [
                'individu' => $this->substitutionService->countAllSubstitutionsIndividu(),
                'doctorant' => $this->substitutionService->countAllSubstitutionsDoctorant(),
                'structure' => $this->substitutionService->countAllSubstitutionsStructure(),
                'etablissement' => $this->substitutionService->countAllSubstitutionsEtablissement(),
                'ecole-doct' => $this->substitutionService->countAllSubstitutionsEcoleDoct(),
                'unite-rech' => $this->substitutionService->countAllSubstitutionsUniteRech(),
            ],
            'doublonsCount' => [
                'individu' => $this->doublonService->countAllDoublonsIndividu(),
                'doctorant' => $this->doublonService->countAllDoublonsDoctorant(),
                'structure' => $this->doublonService->countAllDoublonsStructure(),
                'etablissement' => $this->doublonService->countAllDoublonsEtablissement(),
                'ecole-doct' => $this->doublonService->countAllDoublonsEcoleDoct(),
                'unite-rech' => $this->doublonService->countAllDoublonsUniteRech(),
            ],
        ];
    }
}