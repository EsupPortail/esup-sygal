<?php

namespace Soutenance\Form\QualiteEdition;

trait QualiteEditionFormAwareTrait {

    /** @var QualiteEditionForm */
    private $qualiteEditionForm;

    /**
     * @return QualiteEditionForm
     */
    public function getQualiteEditionForm()
    {
        return $this->qualiteEditionForm;
    }

    /**
     * @param QualiteEditionForm $qualiteEditionForm
     * @return QualiteEditionForm
     */
    public function setQualiteEditionForm($qualiteEditionForm)
    {
        $this->qualiteEditionForm = $qualiteEditionForm;
        return $this->qualiteEditionForm;
    }


}