<?php

namespace Formation\Form\Module;

trait ModuleFormAwareTrait {

    /** @var ModuleForm */
    private $moduleForm;

    /**
     * @return ModuleForm
     */
    public function getModuleForm(): ModuleForm
    {
        return $this->moduleForm;
    }

    /**
     * @param ModuleForm $moduleForm
     * @return ModuleForm
     */
    public function setModuleForm(ModuleForm $moduleForm): ModuleForm
    {
        $this->moduleForm = $moduleForm;
        return $this->moduleForm;
    }
}