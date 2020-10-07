<?php

namespace Soutenance\Form\LabelEuropeen;

trait LabelEuropeenFormAwareTrait {

    /** @var LabelEuropeenForm $labelEtAnglaisForm */
    private $labelEuropeenForm;

    /**
     * @return labelEuropeenForm
     */
    public function getLabelEuropeenForm()
    {
        return $this->labelEuropeenForm;
    }

    /**
     * @param LabelEuropeenForm $labelEuropeenForm
     * @return LabelEuropeenForm
     */
    public function setLabelEuropeenForm($labelEuropeenForm)
    {
        $this->labelEuropeenForm = $labelEuropeenForm;
        return $this->labelEuropeenForm;
    }


}