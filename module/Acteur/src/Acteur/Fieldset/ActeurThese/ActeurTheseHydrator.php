<?php

namespace Acteur\Fieldset\ActeurThese;

use Acteur\Entity\Db\ActeurThese;
use Acteur\Fieldset\AbstractActeurHydrator;
use Acteur\Rule\ActeurThese\ActeurTheseRule;
use Doctrine\Inflector\Inflector;
use Doctrine\Persistence\ObjectManager;

/**
 * @property ActeurTheseRule $acteurRule
 */
class ActeurTheseHydrator extends AbstractActeurHydrator
{
    public function __construct(ObjectManager $objectManager, bool $byValue = true, ?Inflector $inflector = null)
    {
        parent::__construct($objectManager, $byValue, $inflector);

        $this->acteurRule = new ActeurTheseRule();
    }

    public function hydrate(array $data, object $object): ActeurThese
    {
        /** @var ActeurThese $object */

        $data['etablissementForce'] = $data['etablissementForce']['id'] ?? null;

        return parent::hydrate($data, $object);
    }
}