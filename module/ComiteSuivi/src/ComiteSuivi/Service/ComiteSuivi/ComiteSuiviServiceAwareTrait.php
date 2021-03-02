<?php

namespace ComiteSuivi\Service\ComiteSuivi;

trait ComiteSuiviServiceAwareTrait {

    /** @var ComiteSuiviService */
    private $comiteSuiviService;

    /**
     * @return ComiteSuiviService
     */
    public function getComiteSuiviService() : ComiteSuiviService
    {
        return $this->comiteSuiviService;
    }

    /**
     * @param ComiteSuiviService $comiteSuiviService
     * @return ComiteSuiviServiceAwareTrait
     */
    public function setComiteSuiviService(ComiteSuiviService $comiteSuiviService) : ComiteSuiviService
    {
        $this->comiteSuiviService = $comiteSuiviService;
        return $this;
    }


}