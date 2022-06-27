<?php

namespace Structure\Service\Etablissement;

trait EtablissementServiceAwareTrait
{
    /**
     * @var EtablissementService
     */
    protected $etablissementService;

    /**
     * @return EtablissementService
     */
    public function getEtablissementService()
    {
        return $this->etablissementService;
    }

    /**
     * @param EtablissementService $etablissementService
     * @return EtablissementServiceAwareTrait
     */
    public function setEtablissementService($etablissementService)
    {
        $this->etablissementService = $etablissementService;
        return $this;
    }



}