<?php

namespace RapportActivite\Hydrator;

use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\Strategy\DateTimeFormatterStrategy;
use RapportActivite\Entity\AutreActivite;

/**
 * @author Unicaen
 */
class AutreActiviteHydrator extends ClassMethodsHydrator
{
    public function __construct(bool $underscoreSeparatedKeys = true, bool $methodExistsCheck = false)
    {
        parent::__construct($underscoreSeparatedKeys, $methodExistsCheck);

        $this->addStrategy('date', new DateTimeFormatterStrategy('Y-m-d'));
    }

    /**
     * @param array $data
     * @param \RapportActivite\Entity\AutreActivite $object
     * @return \RapportActivite\Entity\AutreActivite
     */
    public function hydrate(array $data, $object): AutreActivite
    {
        $data['temps'] = (int) $data['temps'];

        parent::hydrate($data, $object);

        return $object;
    }
}