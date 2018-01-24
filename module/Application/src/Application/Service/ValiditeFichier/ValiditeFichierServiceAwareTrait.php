<?php

namespace Application\Service\ValiditeFichier;

trait ValiditeFichierServiceAwareTrait
{
    /**
     * @var ValiditeFichierService
     */
    protected $validiteFichierService;

    /**
     * @param ValiditeFichierService $fichierService
     */
    public function setValiditeFichierService(ValiditeFichierService $fichierService)
    {
        $this->validiteFichierService = $fichierService;
    }
}