<?php

namespace Application\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject;

interface DoctrineObjectHydratorAwareInterface
{
    public function setDoctrineObjectHydrator(DoctrineObject $doctrineObjectHydrator);
}