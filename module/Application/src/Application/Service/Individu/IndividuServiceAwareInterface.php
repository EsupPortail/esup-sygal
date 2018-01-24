<?php

namespace Application\Service\Individu;

interface IndividuServiceAwareInterface
{
    public function setIndividuService(IndividuService $individuService);
}