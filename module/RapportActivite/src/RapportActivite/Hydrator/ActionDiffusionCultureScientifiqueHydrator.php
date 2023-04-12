<?php

namespace RapportActivite\Hydrator;

use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\Strategy\DateTimeFormatterStrategy;
use RapportActivite\Entity\ActionDiffusionCultureScientifique;

/**
 * @author Unicaen
 */
class ActionDiffusionCultureScientifiqueHydrator extends ClassMethodsHydrator
{
    public function __construct(bool $underscoreSeparatedKeys = true, bool $methodExistsCheck = false)
    {
        parent::__construct($underscoreSeparatedKeys, $methodExistsCheck);

        $this->addStrategy('date', new DateTimeFormatterStrategy('Y-m-d'));
    }

    /**
     * @param array $data
     * @param \RapportActivite\Entity\ActionDiffusionCultureScientifique $object
     * @return \RapportActivite\Entity\ActionDiffusionCultureScientifique
     */
    public function hydrate(array $data, $object): ActionDiffusionCultureScientifique
    {
        $data['temps'] = (int) $data['temps'];

        parent::hydrate($data, $object);

        return $object;
    }
}