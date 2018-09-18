<?php

namespace Indicateur\Service;

trait IndicateurServiceAwareTrait
{
    /**
     * @var IndicateurService
     */
    protected $indicateurService;

    /**
     * @param IndicateurService $indicateurService
     */
    public function setIndicateurService(IndicateurService $indicateurService)
    {
        $this->indicateurService = $indicateurService;
    }

    public function getIndicateurService()
    {
        return $this->indicateurService;
    }
}