<?php

namespace Application\Entity\Db\Interfaces;

use Application\Entity\Db\TypeRapport;
use RapportActivite\Controller\Recherche\RapportActiviteRechercheController;
use RapportActivite\Service\Search\RapportActiviteSearchService;

trait TypeRapportAwareTrait
{
    /**
     * @var TypeRapport
     */
    protected $typeRapport;

    /**
     * @param TypeRapport $typeRapport
     * @return self
     */
    public function setTypeRapport(TypeRapport $typeRapport): self
    {
        $this->typeRapport = $typeRapport;
        return $this;
    }
}