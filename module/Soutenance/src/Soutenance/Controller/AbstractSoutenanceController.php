<?php

namespace Soutenance\Controller;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use Application\Controller\AbstractController;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRService;
use HDR\Service\HDRServiceAwareTrait;
use InvalidArgumentException;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Entity\PropositionThese;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseServiceAwareTrait;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRServiceAwareTrait;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\These\TheseService;
use These\Service\These\TheseServiceAwareTrait;

/** @method FlashMessenger flashMessenger() */

abstract class AbstractSoutenanceController extends AbstractController
{
    use PropositionTheseServiceAwareTrait;
    use PropositionHDRServiceAwareTrait;
    use TheseServiceAwareTrait;
    use HDRServiceAwareTrait;
    use ActeurTheseServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;
    use ValidationHDRServiceAwareTrait;
    use ValidationTheseServiceAwareTrait;

    protected These|HDR $entity;
    protected PropositionTheseService|PropositionHDRService $propositionService;
    protected TheseService|HDRService $entityService;
    protected ActeurTheseService|ActeurHDRService $acteurService;
    protected ValidationHDRService|ValidationTheseService $validationService;
    protected PropositionHDR|PropositionThese|null $proposition = null;
    protected string $type = "";

    protected function initializeFromType(bool $withValidationService = true,
                                          bool $withEntityService = true,
                                          bool $withActeurService = true): void
    {
        $this->type = $this->params()->fromRoute('type');
        if ($this->type === Proposition::ROUTE_PARAM_PROPOSITION_THESE) {
            $this->entity = $this->requestedThese();
            if($withEntityService) $this->entityService = $this->getTheseService();
            if($withActeurService) $this->acteurService = $this->getActeurTheseService();
            if($withValidationService) $this->validationService = $this->getValidationTheseService();
            $this->propositionService = $this->getPropositionTheseService();
            /** @var PropositionThese $proposition */
            $this->proposition = $this->propositionService->getRepository()->findOneBy(['these' => $this->entity]);
        } elseif ($this->type === Proposition::ROUTE_PARAM_PROPOSITION_HDR) {
            $this->entity = $this->requestedHDR();
            if($withEntityService) $this->entityService = $this->getHDRService();
            if($withActeurService) $this->acteurService = $this->getActeurHDRService();
            if($withValidationService) $this->validationService = $this->getValidationHDRService();
            $this->propositionService = $this->getPropositionHDRService();
            /** @var PropositionHDR $proposition */
            $this->proposition = $this->propositionService->getRepository()->findOneBy(['hdr' => $this->entity]);
        } else {
            throw new InvalidArgumentException('Type invalide : ' . $this->type);
        }
    }

    protected function isHDR(): bool
    {
        return $this->type === Proposition::ROUTE_PARAM_PROPOSITION_HDR;
    }

    protected function isThese(): bool
    {
        return $this->type === Proposition::ROUTE_PARAM_PROPOSITION_THESE;
    }
}