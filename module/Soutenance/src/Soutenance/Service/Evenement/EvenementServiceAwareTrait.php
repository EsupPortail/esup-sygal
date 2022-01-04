<?php

namespace Soutenance\Service\Evenement;

trait EvenementServiceAwareTrait {

    /** @var EvenementService  */
    private $evenementService;

    /**
     * @return EvenementService
     */
    public function getEvenementService(): EvenementService
    {
        return $this->evenementService;
    }

    /**
     * @param EvenementService $evenementService
     * @return EvenementService
     */
    public function setEvenementService(EvenementService $evenementService): EvenementService
    {
        $this->evenementService = $evenementService;
        return $this->evenementService;
    }


}