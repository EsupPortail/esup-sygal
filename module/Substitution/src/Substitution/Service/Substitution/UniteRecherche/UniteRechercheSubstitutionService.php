<?php

namespace Substitution\Service\Substitution\UniteRecherche;

use Application\Service\BaseService;
use Substitution\Constants;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Service\Substitution\SpecificSubstitutionAbstractService;
use Substitution\Service\Substitution\Structure\StructureConcreteSubstitutionServiceTrait;

class UniteRechercheSubstitutionService extends SpecificSubstitutionAbstractService
{
    use StructureConcreteSubstitutionServiceTrait;

    protected string $type = Constants::TYPE_unite_rech;

    /**
     * @var \Structure\Service\UniteRecherche\UniteRechercheService
     */
    protected BaseService $entityService;

    public function saveEntity(SubstitutionAwareEntityInterface $entity): void
    {
        /** @var \Structure\Entity\Db\UniteRecherche $entity */
        $this->entityService->update($entity);
    }
}