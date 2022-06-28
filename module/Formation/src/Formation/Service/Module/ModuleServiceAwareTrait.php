<?php

namespace Formation\Service\Module;

trait ModuleServiceAwareTrait
{
    private ModuleService $moduleService;

    /**
     * @return ModuleService
     */
    public function getModuleService(): ModuleService
    {
        return $this->moduleService;
    }

    /**
     * @param ModuleService $moduleService
     */
    public function setModuleService(ModuleService $moduleService): void
    {
        $this->moduleService = $moduleService;
    }

}