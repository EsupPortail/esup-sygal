<?php

namespace Soutenance\Service\QualiteLibelleSupplementaire;

trait QualiteLibelleSupplementaireServiceAwareTrait {

    /** @var QualiteLibelleSupplementaireService */
    private $qualiteLibelleSupplementaireService;

    /**
     * @return QualiteLibelleSupplementaireService
     */
    public function getQualiteLibelleSupplementaireService()
    {
        return $this->qualiteLibelleSupplementaireService;
    }

    /**
     * @param QualiteLibelleSupplementaireService $qualiteLibelleSupplementaireService
     * @return QualiteLibelleSupplementaireService
     */
    public function setQualiteLibelleSupplementaireService($qualiteLibelleSupplementaireService)
    {
        $this->qualiteLibelleSupplementaireService = $qualiteLibelleSupplementaireService;
        return $this->qualiteLibelleSupplementaireService;
    }
}