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
     * @param ModuleService $formationService
     * @return ModuleService
     */
    public function setModuleService(ModuleService $formationService): ModuleService
    {
        $this->moduleService = $formationService;
        return $this->moduleService;
    }

}