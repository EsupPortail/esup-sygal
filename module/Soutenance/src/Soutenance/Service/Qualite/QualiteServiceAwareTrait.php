<?php

namespace Soutenance\Service\Qualite;

trait QualiteServiceAwareTrait {

    /** @var QualiteService */
    private $qualiteService;

    /**
     * @return QualiteService
     */
    public function getQualiteService()
    {
        return $this->qualiteService;
    }

    /**
     * @param QualiteService $qualiteService
     * @return QualiteService
     */
    public function setQualiteService($qualiteService)
    {
        $this->qualiteService = $qualiteService;
        return $this->qualiteService;
    }

}