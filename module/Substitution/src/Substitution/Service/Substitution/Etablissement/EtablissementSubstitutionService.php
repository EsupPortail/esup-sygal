<?php

namespace Substitution\Service\Substitution\Etablissement;

use Application\Service\BaseService;
use Substitution\Constants;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Service\Substitution\SpecificSubstitutionAbstractService;
use Substitution\Service\Substitution\Structure\StructureConcreteSubstitutionServiceTrait;

class EtablissementSubstitutionService extends SpecificSubstitutionAbstractService
{
    use StructureConcreteSubstitutionServiceTrait;

    protected string $type = Constants::TYPE_etablissement;

    /**
     * @var \Structure\Service\Etablissement\EtablissementService
     */
    protected BaseService $entityService;

    public function saveEntity(SubstitutionAwareEntityInterface $entity): void
    {
        /** @var \Structure\Entity\Db\Etablissement $entity */
        $this->entityService->update($entity);
    }
}