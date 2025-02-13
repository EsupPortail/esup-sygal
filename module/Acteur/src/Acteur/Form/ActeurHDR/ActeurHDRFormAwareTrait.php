<?php

namespace Acteur\Form\ActeurHDR;

trait ActeurHDRFormAwareTrait
{
    private ActeurHDRForm $acteurHDRForm;

    public function getActeurHDRForm(): ActeurHDRForm
    {
        return $this->acteurHDRForm;
    }

    public function setActeurHDRForm(ActeurHDRForm $acteurHDRForm): void
    {
        $this->acteurHDRForm = $acteurHDRForm;
    }


}