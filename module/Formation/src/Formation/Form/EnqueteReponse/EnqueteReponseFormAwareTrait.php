<?php

namespace Formation\Form\EnqueteReponse;

trait EnqueteReponseFormAwareTrait {

    /** @var EnqueteReponseForm */
    private $enqueteReponseForm;

    /**
     * @return EnqueteReponseForm
     */
    public function getEnqueteReponseForm(): EnqueteReponseForm
    {
        return $this->enqueteReponseForm;
    }

    /**
     * @param EnqueteReponseForm $enqueteReponseForm
     * @return EnqueteReponseForm
     */
    public function setEnqueteReponseForm(EnqueteReponseForm $enqueteReponseForm): EnqueteReponseForm
    {
        $this->enqueteReponseForm = $enqueteReponseForm;
        return $this->enqueteReponseForm;
    }
}