<?php

namespace ComiteSuivi\Service\ComiteSuivi;

trait ComiteSuiviServiceAwareTrait {

    /** @var ComiteSuiviService */
    private $comiteSuiviService;

    /**
     * @return ComiteSuiviService
     */
    public function getComiteSuiviService()
    {
        return $this->comiteSuiviService;
    }

    /**
     * @param ComiteSuiviService $comiteSuiviService
     * @return ComiteSuiviServiceAwareTrait
     */
    public function setComiteSuiviService($comiteSuiviService)
    {
        $this->comiteSuiviService = $comiteSuiviService;
        return $this;
    }


}