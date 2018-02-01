<?php

namespace Application\Service\Etablissement;

trait EtablissementServiceAwareTrait
{
    /**
     * @var EtablissementService
     */
    protected $etablissementService;

    /**
     * @param EtablissementService $etablissementService
     */
    public function setEtablissementService(EtablissementService $etablissementService)
    {
        $this->etablissementService = $etablissementService;
    }
}