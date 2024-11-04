<?php

namespace These\Form\Generalites;


trait GeneralitesFormAwareTrait {

    /** @var GeneralitesForm  */
    private GeneralitesForm $generalitesForm;

    /**
     * @return GeneralitesForm
     */
    public function getGeneralitesForm(): GeneralitesForm
    {
        return $this->generalitesForm;
    }

    /**
     * @param GeneralitesForm $generalitesForm
     */
    public function setGeneralitesForm(GeneralitesForm $generalitesForm): void
    {
        $this->generalitesForm = $generalitesForm;
    }
}