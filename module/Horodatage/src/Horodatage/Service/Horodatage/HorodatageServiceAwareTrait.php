<?php

namespace Horodatage\Service\Horodatage;

trait HorodatageServiceAwareTrait {

    private HorodatageService $horodatageService;

    public function getHorodatageService(): HorodatageService
    {
        return $this->horodatageService;
    }

    public function setHorodatageService(HorodatageService $horodatageService): void
    {
        $this->horodatageService = $horodatageService;
    }

}