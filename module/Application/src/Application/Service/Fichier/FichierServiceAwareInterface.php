<?php

namespace Application\Service\Fichier;

interface FichierServiceAwareInterface
{
    public function setFichierService(FichierService $fichierService);
}