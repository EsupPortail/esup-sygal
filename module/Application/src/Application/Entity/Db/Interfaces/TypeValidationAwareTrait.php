<?php

namespace Application\Entity\Db\Interfaces;

use Application\Entity\Db\TypeValidation;
use RapportActivite\Controller\Recherche\RapportActiviteRechercheController;
use RapportActivite\Service\Search\RapportActiviteSearchService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;

trait TypeValidationAwareTrait
{
    /**
     * @var TypeValidation
     */
    protected $typeValidation;

    /**
     * @param TypeValidation $typeValidation
     * @return self
     */
    public function setTypeValidation(TypeValidation $typeValidation): self
    {
        $this->typeValidation = $typeValidation;
        return $this;
    }
}