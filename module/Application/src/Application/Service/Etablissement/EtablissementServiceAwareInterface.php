<?php

namespace Application\Service\Etablissement;

interface EtablissementServiceAwareInterface
{
    public function setEtablissementService(EtablissementService $envService);
}