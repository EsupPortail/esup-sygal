<?php

namespace Application\Service\VersionFichier;

interface VersionFichierServiceAwareInterface
{
    public function setVersionFichierService(VersionFichierService $theseService);
}