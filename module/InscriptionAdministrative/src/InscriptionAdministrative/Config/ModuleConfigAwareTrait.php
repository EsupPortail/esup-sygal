<?php

namespace InscriptionAdministrative\Config;

trait ModuleConfigAwareTrait
{
    protected ModuleConfig $moduleConfig;

    public function setModuleConfig(ModuleConfig $moduleConfig): void
    {
        $this->moduleConfig = $moduleConfig;
    }
}