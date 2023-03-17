<?php

namespace Application\Service;

use UnicaenPrivilege\Service\AuthorizeService;

interface AuthorizeServiceAwareInterface
{
    public function setAuthorizeService(AuthorizeService $service);
}