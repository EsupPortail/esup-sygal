<?php

namespace Soutenance\Form\Confidentialite;

trait ConfidentialiteFormAwareTrait {

    /** @var ConfigurationForm */
    private $confidentialiteForm;

    /**
     * @return ConfigurationForm
     */
    public function getConfidentialiteForm()
    {
        return $this->confidentialiteForm;
    }

    /**
     * @param ConfigurationForm $confidentialiteForm
     * @return ConfigurationForm
     */
    public function setConfidentialiteForm($confidentialiteForm)
    {
        $this->confidentialiteForm = $confidentialiteForm;
        return $this->confidentialiteForm;
    }


}