<?php

namespace Formation\Service\EnqueteReponse;

trait EnqueteReponseServiceAwareTrait
{
    /** @var EnqueteReponseService */
    private $enqueteReponseService;

    /**
     * @return EnqueteReponseService
     */
    public function getEnqueteReponseService(): EnqueteReponseService
    {
        return $this->enqueteReponseService;
    }

    /**
     * @param EnqueteReponseService $enqueteReponseService
     * @return EnqueteReponseService
     */
    public function setEnqueteReponseService(EnqueteReponseService $enqueteReponseService): EnqueteReponseService
    {
        $this->enqueteReponseService = $enqueteReponseService;
        return $this->enqueteReponseService;
    }

}