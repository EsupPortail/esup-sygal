<?php

namespace Substitution\Service\Substitution\EcoleDoctorale;

use Application\Service\BaseService;
use Substitution\Constants;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Service\Substitution\SpecificSubstitutionAbstractService;
use Substitution\Service\Substitution\Structure\StructureConcreteSubstitutionServiceTrait;

class EcoleDoctoraleSubstitutionService extends SpecificSubstitutionAbstractService
{
    use StructureConcreteSubstitutionServiceTrait;

    protected string $type = Constants::TYPE_ecole_doct;

    /**
     * @var \Structure\Service\EcoleDoctorale\EcoleDoctoraleService
     */
    protected BaseService $entityService;

    public function saveEntity(SubstitutionAwareEntityInterface $entity): void
    {
        /** @var \Structure\Entity\Db\EcoleDoctorale $entity */
        $this->entityService->update($entity);
    }
}