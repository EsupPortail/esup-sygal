<?php

namespace Soutenance\Form\Avis;

trait AvisFormAwareTrait
{

    /** @var AvisForm */
    private $avisForm;

    /**
     * @return AvisForm
     */
    public function getAvisForm()
    {
        return $this->avisForm;
    }

    /**
     * @param AvisForm $avisForm
     * @return AvisForm
     */
    public function setAvisForm($avisForm)
    {
        $this->avisForm = $avisForm;
        return $this->avisForm;
    }


}