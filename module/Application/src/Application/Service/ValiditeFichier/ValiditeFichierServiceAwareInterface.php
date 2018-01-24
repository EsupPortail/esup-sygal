<?php

namespace Application\Service\ValiditeFichier;

interface ValiditeFichierServiceAwareInterface
{
    public function setValiditeFichierService(ValiditeFichierService $fichierService);
}