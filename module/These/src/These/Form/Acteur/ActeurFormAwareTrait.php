<?php

namespace These\Form\Acteur;

trait ActeurFormAwareTrait
{
    private ActeurForm $acteurForm;

    public function getActeurForm(): ActeurForm
    {
        return $this->acteurForm;
    }

    public function setActeurForm(ActeurForm $acteurForm): void
    {
        $this->acteurForm = $acteurForm;
    }


}