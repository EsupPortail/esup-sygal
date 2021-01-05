<?php

namespace Application\Form;

trait RechercherCoEncadrantFormAwareTrait {

    /** @var RechercherCoEncadrantForm */
    private $rechercherCoEncadrantForm;

    /**
     * @return RechercherCoEncadrantForm
     */
    public function getRechercherCoEncadrantForm(): RechercherCoEncadrantForm
    {
        return $this->rechercherCoEncadrantForm;
    }

    /**
     * @param RechercherCoEncadrantForm $rechercherCoEncadrantForm
     * @return RechercherCoEncadrantForm
     */
    public function setRechercherCoEncadrantForm(RechercherCoEncadrantForm $rechercherCoEncadrantForm): RechercherCoEncadrantForm
    {
        $this->rechercherCoEncadrantForm = $rechercherCoEncadrantForm;
        return $this->rechercherCoEncadrantForm;
    }


}