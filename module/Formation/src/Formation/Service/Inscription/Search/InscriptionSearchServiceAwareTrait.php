<?php

namespace Formation\Service\Inscription\Search;

trait InscriptionSearchServiceAwareTrait
{
    private InscriptionSearchService $inscriptionSearchService;

    /**
     * @return InscriptionSearchService
     */
    public function getInscriptionSearchService(): InscriptionSearchService
    {
        return $this->inscriptionSearchService;
    }

    /**
     * @param InscriptionSearchService $inscriptionSearchService
     * @return InscriptionSearchService
     */
    public function setInscriptionSearchService(InscriptionSearchService $inscriptionSearchService): InscriptionSearchService
    {
        $this->inscriptionSearchService = $inscriptionSearchService;
        return $this->inscriptionSearchService;
    }

}