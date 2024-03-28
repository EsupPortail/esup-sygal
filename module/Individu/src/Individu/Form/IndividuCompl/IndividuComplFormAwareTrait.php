<?php

namespace Individu\Form\IndividuCompl;

trait IndividuComplFormAwareTrait {

    protected IndividuComplForm $individuComplForm;

    public function setIndividuComplForm(IndividuComplForm $individuComplForm): void
    {
        $this->individuComplForm = $individuComplForm;
    }

}