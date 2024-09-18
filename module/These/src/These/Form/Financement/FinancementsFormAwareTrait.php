<?php

namespace These\Form\Financement;

trait FinancementsFormAwareTrait {

    /** @var FinancementsForm  $financementsForm*/
    private FinancementsForm $financementsForm;

    /**
     * @return FinancementsForm
     */
    public function getFinancementsForm(): FinancementsForm
    {
        return $this->financementsForm;
    }

    /**
     * @param FinancementsForm $financementsForm
     */
    public function setFinancementsForm(FinancementsForm $financementsForm): void
    {
        $this->financementsForm = $financementsForm;
    }
}