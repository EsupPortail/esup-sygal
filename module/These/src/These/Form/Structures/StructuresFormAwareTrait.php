<?php

namespace These\Form\Structures;

trait StructuresFormAwareTrait {

    /** @var StructuresForm  */
    private StructuresForm $structuresForm;

    /**
     * @return StructuresForm
     */
    public function getStructuresForm(): StructuresForm
    {
        return $this->structuresForm;
    }

    /**
     * @param StructuresForm $structuresForm
     */
    public function setStructuresForm(StructuresForm $structuresForm): void
    {
        $this->structuresForm = $structuresForm;
    }
}