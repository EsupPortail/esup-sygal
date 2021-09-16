<?php

namespace Formation\Service\EnqueteCategorie;

trait EnqueteCategorieServiceAwareTrait
{
    /** @var EnqueteCategorieService */
    private $enqueteCategorieService;

    /**
     * @return EnqueteCategorieService
     */
    public function getEnqueteCategorieService(): EnqueteCategorieService
    {
        return $this->enqueteCategorieService;
    }

    /**
     * @param EnqueteCategorieService $enqueteCategorieService
     * @return EnqueteCategorieService
     */
    public function setEnqueteCategorieService(EnqueteCategorieService $enqueteCategorieService): EnqueteCategorieService
    {
        $this->enqueteCategorieService = $enqueteCategorieService;
        return $this->enqueteCategorieService;
    }

}