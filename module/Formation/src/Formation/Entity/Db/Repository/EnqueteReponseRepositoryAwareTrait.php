<?php

namespace Formation\Entity\Db\Repository;

trait EnqueteReponseRepositoryAwareTrait
{
    protected EnqueteReponseRepository $enqueteReponseRepository;

    /**
     * @param \Formation\Entity\Db\Repository\EnqueteReponseRepository $enqueteReponseRepository
     */
    public function setEnqueteReponseRepository(EnqueteReponseRepository $enqueteReponseRepository): void
    {
        $this->enqueteReponseRepository = $enqueteReponseRepository;
    }
}

