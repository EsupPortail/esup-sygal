<?php

namespace Fichier\Service\Fichier;

interface FichierServiceAwareInterface
{
    public function setFichierService(FichierService $fichierService);
}