<?php

namespace Soutenance\Form\QualiteLibelleSupplementaire;

trait QualiteLibelleSupplementaireFormAwareTrait {

    /** @var QualiteLibelleSupplementaireForm */
    private $qualiteLibelleSupplementaireForm;

    /**
     * @return QualiteLibelleSupplementaireForm
     */
    public function getQualiteLibelleSupplementaireForm()
    {
        return $this->qualiteLibelleSupplementaireForm;
    }

    /**
     * @param QualiteLibelleSupplementaireForm $qualiteLibelleSupplementaireForm
     * @return QualiteLibelleSupplementaireForm
     */
    public function setQualiteLibelleSupplementaireForm($qualiteLibelleSupplementaireForm)
    {
        $this->qualiteLibelleSupplementaireForm = $qualiteLibelleSupplementaireForm;
        return $this->qualiteLibelleSupplementaireForm;
    }


}