<?php

namespace Retraitement\Service;

interface RetraitementServiceAwareInterface
{
    public function setRetraitementService(RetraitementService $service);
}