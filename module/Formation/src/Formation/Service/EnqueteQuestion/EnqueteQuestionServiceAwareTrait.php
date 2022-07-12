<?php

namespace Formation\Service\EnqueteQuestion;

trait EnqueteQuestionServiceAwareTrait
{
    /** @var EnqueteQuestionService */
    private $enqueteQuestionService;

    /**
     * @return EnqueteQuestionService
     */
    public function getEnqueteQuestionService(): EnqueteQuestionService
    {
        return $this->enqueteQuestionService;
    }

    /**
     * @param EnqueteQuestionService $enqueteQuestionService
     * @return EnqueteQuestionService
     */
    public function setEnqueteQuestionService(EnqueteQuestionService $enqueteQuestionService): EnqueteQuestionService
    {
        $this->enqueteQuestionService = $enqueteQuestionService;
        return $this->enqueteQuestionService;
    }

}