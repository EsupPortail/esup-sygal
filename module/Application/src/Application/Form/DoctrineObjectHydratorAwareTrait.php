<?php

namespace Application\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject;

trait DoctrineObjectHydratorAwareTrait
{
    /**
     * @var DoctrineObject
     */
    protected $doctrineObjectHydrator;

    /**
     * @param DoctrineObject $doctrineObjectHydrator
     */
    public function setDoctrineObjectHydrator(DoctrineObject $doctrineObjectHydrator)
    {
        $this->doctrineObjectHydrator = $doctrineObjectHydrator;
    }
}