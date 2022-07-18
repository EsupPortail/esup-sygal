<?php

namespace Formation\Form\Module;

trait ModuleFormAwareTrait {

    private ModuleForm $moduleForm;

    /**
     * @return ModuleForm
     */
    public function getModuleForm(): ModuleForm
    {
        return $this->moduleForm;
    }

    /**
     * @param ModuleForm $moduleForm
     */
    public function setModuleForm(ModuleForm $moduleForm): void
    {
        $this->moduleForm = $moduleForm;
    }
}