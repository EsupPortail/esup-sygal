<?php

namespace Formation\Entity\Db\Repository;

trait FormationExterneRepositoryAwareTrait
{
    protected FormationExterneRepository $formationExterneRepository;

    /**
     * @param \Formation\Entity\Db\Repository\FormationExterneRepository $formationExterneRepository
     */
    public function setFormationExterneRepository(FormationExterneRepository $formationExterneRepository): void
    {
        $this->formationExterneRepository = $formationExterneRepository;
    }
}

