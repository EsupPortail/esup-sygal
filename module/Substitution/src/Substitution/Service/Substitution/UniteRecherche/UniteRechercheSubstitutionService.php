<?php

namespace Substitution\Service\Substitution\UniteRecherche;

use Application\Service\BaseService;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Service\Substitution\SpecificSubstitutionAbstractService;
use Substitution\Service\Substitution\Structure\StructureConcreteSubstitutionServiceTrait;

class UniteRechercheSubstitutionService extends SpecificSubstitutionAbstractService
{
    use StructureConcreteSubstitutionServiceTrait;

    /**
     * @var \Structure\Service\UniteRecherche\UniteRechercheService
     */
    protected BaseService $entityService;

    public function __construct()
    {
        $this->type = 'unite_rech';
    }

    public function saveEntity(SubstitutionAwareEntityInterface $entity): void
    {
        /** @var \Structure\Entity\Db\UniteRecherche $entity */
        $this->entityService->update($entity);
    }
}