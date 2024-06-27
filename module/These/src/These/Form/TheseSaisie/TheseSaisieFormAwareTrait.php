<?php

namespace These\Form\TheseSaisie;

trait TheseSaisieFormAwareTrait {

    /** @var TheseSaisieForm  */
    private TheseSaisieForm $theseSaisieForm;

    /**
     * @return TheseSaisieForm
     */
    public function getTheseSaisieForm(): TheseSaisieForm
    {
        return $this->theseSaisieForm;
    }

    /**
     * @param TheseSaisieForm $theseSaisieForm
     */
    public function setTheseSaisieForm(TheseSaisieForm $theseSaisieForm): void
    {
        $this->theseSaisieForm = $theseSaisieForm;
    }


}