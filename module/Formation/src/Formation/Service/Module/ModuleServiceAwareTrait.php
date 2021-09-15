<?php

namespace Formation\Service\Module;

trait ModuleServiceAwareTrait
{
    /** @var ModuleService */
    private $moduleService;

    /**
     * @return ModuleService
     */
    public function getModuleService(): ModuleService
    {
        return $this->moduleService;
    }

    /**
     * @param ModuleService $moduleService
     * @return ModuleService
     */
    public function setModuleService(ModuleService $moduleService): ModuleService
    {
        $this->moduleService = $moduleService;
        return $this->moduleService;
    }

}