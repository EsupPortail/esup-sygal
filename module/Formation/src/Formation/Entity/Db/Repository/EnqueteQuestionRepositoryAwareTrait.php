<?php

namespace Formation\Entity\Db\Repository;

trait EnqueteQuestionRepositoryAwareTrait
{
    protected EnqueteQuestionRepository $enqueteQuestionRepository;

    /**
     * @param \Formation\Entity\Db\Repository\EnqueteQuestionRepository $enqueteQuestionRepository
     */
    public function setEnqueteQuestionRepository(EnqueteQuestionRepository $enqueteQuestionRepository): void
    {
        $this->enqueteQuestionRepository = $enqueteQuestionRepository;
    }
}

