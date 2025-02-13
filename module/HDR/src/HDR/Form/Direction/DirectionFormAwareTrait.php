<?php

namespace HDR\Form\Direction;

trait DirectionFormAwareTrait {

    /** @var DirectionForm  $directionForm*/
    private DirectionForm $directionForm;

    /**
     * @return DirectionForm
     */
    public function getDirectionForm(): DirectionForm
    {
        return $this->directionForm;
    }

    /**
     * @param DirectionForm $directionForm
     */
    public function setDirectionForm(DirectionForm $directionForm): void
    {
        $this->directionForm = $directionForm;
    }
}