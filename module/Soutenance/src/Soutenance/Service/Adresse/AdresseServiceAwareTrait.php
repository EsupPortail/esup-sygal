<?php

namespace Soutenance\Service\Adresse;

trait AdresseServiceAwareTrait
{
    private AdresseService $adresseService;

    public function getAdresseService(): AdresseService
    {
        return $this->adresseService;
    }

    public function setAdresseService(AdresseService $adresseService): void
    {
        $this->adresseService = $adresseService;
    }


}