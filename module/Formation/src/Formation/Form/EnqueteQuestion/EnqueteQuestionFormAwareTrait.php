<?php

namespace Formation\Form\EnqueteQuestion;

trait EnqueteQuestionFormAwareTrait {

    /** @var EnqueteQuestionForm */
    private $enqueteQuestionForm;

    /**
     * @return EnqueteQuestionForm
     */
    public function getEnqueteQuestionForm(): EnqueteQuestionForm
    {
        return $this->enqueteQuestionForm;
    }

    /**
     * @param EnqueteQuestionForm $enqueteQuestionForm
     * @return EnqueteQuestionForm
     */
    public function setEnqueteQuestionForm(EnqueteQuestionForm $enqueteQuestionForm): EnqueteQuestionForm
    {
        $this->enqueteQuestionForm = $enqueteQuestionForm;
        return $this->enqueteQuestionForm;
    }


}