<?php

namespace These\Service\CoEncadrant;

trait CoEncadrantServiceAwareTrait {

    /** @var CoEncadrantService */
    private $coEncadrantService;

    /**
     * @return CoEncadrantService
     */
    public function getCoEncadrantService(): CoEncadrantService
    {
        return $this->coEncadrantService;
    }

    /**
     * @param CoEncadrantService $coEncadrantService
     * @return CoEncadrantService
     */
    public function setCoEncadrantService(CoEncadrantService $coEncadrantService): CoEncadrantService
    {
        $this->coEncadrantService = $coEncadrantService;
        return $this->coEncadrantService;
    }
}