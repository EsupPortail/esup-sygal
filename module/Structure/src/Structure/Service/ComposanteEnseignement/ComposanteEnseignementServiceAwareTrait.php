<?php

namespace Structure\Service\ComposanteEnseignement;

trait ComposanteEnseignementServiceAwareTrait
{
    /**
     * @var ComposanteEnseignementService
     */
    protected $composanteEnseignementService;

    /**
     * @param ComposanteEnseignementService $composanteEnseignementService
     */
    public function setComposanteEnseignementService(ComposanteEnseignementService $composanteEnseignementService)
    {
        $this->composanteEnseignementService = $composanteEnseignementService;
    }

    public function getComposanteEnseignementService() {
        return $this->composanteEnseignementService;
    }
}