<?php

namespace Application\Service;

use UnicaenAuth\Service\AuthorizeService;

interface AuthorizeServiceAwareInterface
{
    public function setAuthorizeService(AuthorizeService $service);
}