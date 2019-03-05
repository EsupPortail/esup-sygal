<?php

namespace Soutenance\Form\LabelEtAnglais;

trait LabelEtAnglaisAwareTrait {

    /** @var LabelEtAnglaisForm $labelEtAnglaisForm */
    private $labelEtAnglaisForm;

    /**
     * @return LabelEtAnglaisForm
     */
    public function getLabelEtAnglaisForm()
    {
        return $this->labelEtAnglaisForm;
    }

    /**
     * @param LabelEtAnglaisForm $labelEtAnglaisForm
     * @return LabelEtAnglaisForm
     */
    public function setLabelEtAnglaisForm($labelEtAnglaisForm)
    {
        $this->labelEtAnglaisForm = $labelEtAnglaisForm;
        return $this->labelEtAnglaisForm;
    }


}