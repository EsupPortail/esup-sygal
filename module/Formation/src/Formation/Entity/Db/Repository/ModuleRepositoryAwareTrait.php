<?php

namespace Formation\Entity\Db\Repository;

trait ModuleRepositoryAwareTrait
{
    protected ModuleRepository $moduleRepository;

    /**
     * @param \Formation\Entity\Db\Repository\ModuleRepository $moduleRepository
     */
    public function setModuleRepository(ModuleRepository $moduleRepository): void
    {
        $this->moduleRepository = $moduleRepository;
    }
}

