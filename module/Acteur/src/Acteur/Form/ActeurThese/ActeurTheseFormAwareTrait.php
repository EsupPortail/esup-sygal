<?php

namespace Acteur\Form\ActeurThese;

trait ActeurTheseFormAwareTrait
{
    private ActeurTheseForm $acteurTheseForm;

    public function getActeurTheseForm(): ActeurTheseForm
    {
        return $this->acteurTheseForm;
    }

    public function setActeurTheseForm(ActeurTheseForm $acteurTheseForm): void
    {
        $this->acteurTheseForm = $acteurTheseForm;
    }


}