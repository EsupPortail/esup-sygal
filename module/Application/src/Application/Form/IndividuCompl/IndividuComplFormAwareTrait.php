<?php

namespace Application\Form\IndividuCompl;

trait IndividuComplFormAwareTrait {

    /** @var IndividuComplForm */
    private $individuComplForm;

    /**
     * @return IndividuComplForm
     */
    public function getIndividuComplForm(): IndividuComplForm
    {
        return $this->individuComplForm;
    }

    /**
     * @param IndividuComplForm $individuComplForm
     */
    public function setIndividuComplForm(IndividuComplForm $individuComplForm): void
    {
        $this->individuComplForm = $individuComplForm;
    }

}