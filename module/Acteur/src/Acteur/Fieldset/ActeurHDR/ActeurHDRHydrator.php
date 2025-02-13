<?php

namespace Acteur\Fieldset\ActeurHDR;

use Acteur\Fieldset\AbstractActeurHydrator;
use Acteur\Rule\ActeurHDR\ActeurHDRRule;
use Doctrine\Inflector\Inflector;
use Doctrine\Persistence\ObjectManager;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;

/**
 * @property ActeurHDRRule $acteurRule
 */
class ActeurHDRHydrator extends AbstractActeurHydrator
{
    use QualiteServiceAwareTrait;
    public function __construct(ObjectManager $objectManager, bool $byValue = true, ?Inflector $inflector = null)
    {
        parent::__construct($objectManager, $byValue, $inflector);

        $this->acteurRule = new ActeurHDRRule();
    }
}