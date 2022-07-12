<?php

namespace Formation\Entity\Db\Repository;

trait EnqueteCategorieRepositoryAwareTrait
{
    protected EnqueteCategorieRepository $enqueteCategorieRepository;

    /**
     * @param \Formation\Entity\Db\Repository\EnqueteCategorieRepository $enqueteCategorieRepository
     */
    public function setEnqueteCategorieRepository(EnqueteCategorieRepository $enqueteCategorieRepository): void
    {
        $this->enqueteCategorieRepository = $enqueteCategorieRepository;
    }
}

