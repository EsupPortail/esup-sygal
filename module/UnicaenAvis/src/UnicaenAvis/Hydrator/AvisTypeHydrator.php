<?php

namespace UnicaenAvis\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class AvisTypeHydrator extends DoctrineObject
{
    use AvisServiceAwareTrait;
}