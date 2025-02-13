<?php

namespace HDR\Form\HDRSaisie;

trait HDRSaisieFormAwareTrait {

    /** @var HDRSaisieForm  */
    private HDRSaisieForm $hdrSaisieForm;

    /**
     * @return HDRSaisieForm
     */
    public function getHDRSaisieForm(): HDRSaisieForm
    {
        return $this->hdrSaisieForm;
    }

    /**
     * @param HDRSaisieForm $hdrSaisieForm
     */
    public function setHDRSaisieForm(HDRSaisieForm $hdrSaisieForm): void
    {
        $this->hdrSaisieForm = $hdrSaisieForm;
    }


}