<?php

namespace Application\Service\Acteur;

trait ActeurServiceAwareTrait
{
    /**
     * @var ActeurService
     */
    protected $acteurService;

    /**
     * @param ActeurService $acteurService
     */
    public function setActeurService(ActeurService $acteurService)
    {
        $this->acteurService = $acteurService;
    }

    /**
     * @return ActeurService
     */
    public function getActeurService()
    {
        return $this->acteurService;
    }
}