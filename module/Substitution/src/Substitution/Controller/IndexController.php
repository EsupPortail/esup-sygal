<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Substitution\Constants;
use Substitution\Service\Doublon\DoublonServiceAwareTrait;
use Substitution\Service\Substitution\SubstitutionServiceAwareTrait;

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
                'individu' => $this->substitutionService->countAllSubstitutionsForType(Constants::TYPE_individu),
                'doctorant' => $this->substitutionService->countAllSubstitutionsForType(Constants::TYPE_doctorant),
                'structure' => $this->substitutionService->countAllSubstitutionsForType(Constants::TYPE_structure),
                'etablissement' => $this->substitutionService->countAllSubstitutionsForType(Constants::TYPE_etablissement),
                'ecole-doct' => $this->substitutionService->countAllSubstitutionsForType(Constants::TYPE_ecole_doct),
                'unite-rech' => $this->substitutionService->countAllSubstitutionsForType(Constants::TYPE_unite_rech),
            ],
            'doublonsCount' => [
                'individu' => $this->doublonService->countAllDoublonsForType(Constants::TYPE_individu),
                'doctorant' => $this->doublonService->countAllDoublonsForType(Constants::TYPE_doctorant),
                'structure' => $this->doublonService->countAllDoublonsForType(Constants::TYPE_structure),
                'etablissement' => $this->doublonService->countAllDoublonsForType(Constants::TYPE_etablissement),
                'ecole-doct' => $this->doublonService->countAllDoublonsForType(Constants::TYPE_ecole_doct),
                'unite-rech' => $this->doublonService->countAllDoublonsForType(Constants::TYPE_unite_rech),
            ],
        ];
    }
}