<?php

namespace Formation\Entity\Db\Repository;

trait FormationRepositoryAwareTrait
{
    protected FormationRepository $formationRepository;

    /**
     * @param \Formation\Entity\Db\Repository\FormationRepository $formationRepository
     */
    public function setFormationRepository(FormationRepository $formationRepository): void
    {
        $this->formationRepository = $formationRepository;
    }
}

