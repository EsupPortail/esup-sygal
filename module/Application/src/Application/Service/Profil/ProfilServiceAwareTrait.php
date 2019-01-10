<?php

namespace Application\Service\Profil;

trait  ProfilServiceAwareTrait {

    /** @var ProfilService */
    private $profilService;

    /**
     * @param ProfilService $profilService
     * @return ProfilService
     */
    public function setProfilService($profilService)
    {
        $this->profilService = $profilService;
        return $this->profilService;
    }

    /**
     * @return ProfilService
     */
    public function getProfilService()
    {
        return $this->profilService;
    }

}