<?php

namespace Formation\Form\EnqueteCategorie;

trait EnqueteCategorieFormAwareTrait {

    /** @var EnqueteCategorieForm */
    private $enqueteCategorieForm;

    /**
     * @return EnqueteCategorieForm
     */
    public function getEnqueteCategorieForm(): EnqueteCategorieForm
    {
        return $this->enqueteCategorieForm;
    }

    /**
     * @param EnqueteCategorieForm $enqueteCategorieForm
     * @return EnqueteCategorieForm
     */
    public function setEnqueteCategorieForm(EnqueteCategorieForm $enqueteCategorieForm): EnqueteCategorieForm
    {
        $this->enqueteCategorieForm = $enqueteCategorieForm;
        return $this->enqueteCategorieForm;
    }


}