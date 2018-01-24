<?php

namespace Application\Service\These;

interface TheseServiceAwareInterface
{
    public function setTheseService(TheseService $theseService);
}