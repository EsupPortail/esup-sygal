<?php

namespace Application\Service;

interface UserContextServiceAwareInterface
{
    public function setUserContextService(UserContextService $service);
}