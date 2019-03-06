<?php

namespace Soutenance\Form\Confidentialite;

trait ConfidentialiteFormAwareTrait {

    /** @var ConfidentialiteForm */
    private $confidentialiteForm;

    /**
     * @return ConfidentialiteForm
     */
    public function getConfidentialiteForm()
    {
        return $this->confidentialiteForm;
    }

    /**
     * @param ConfidentialiteForm $confidentialiteForm
     * @return ConfidentialiteForm
     */
    public function setConfidentialiteForm($confidentialiteForm)
    {
        $this->confidentialiteForm = $confidentialiteForm;
        return $this->confidentialiteForm;
    }


}