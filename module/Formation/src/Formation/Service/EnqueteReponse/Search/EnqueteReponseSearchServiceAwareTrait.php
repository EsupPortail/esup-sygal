<?php

namespace Formation\Service\EnqueteReponse\Search;

trait EnqueteReponseSearchServiceAwareTrait
{
    protected EnqueteReponseSearchService $enqueteReponseSearchService;

    public function setEnqueteReponseSearchService(EnqueteReponseSearchService $enqueteReponseSearchService): EnqueteReponseSearchService
    {
        $this->enqueteReponseSearchService = $enqueteReponseSearchService;
        return $this->enqueteReponseSearchService;
    }

}