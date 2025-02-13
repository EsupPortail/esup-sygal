<?php

namespace Acteur\Fieldset\ActeurHDR;

use Acteur\Fieldset\AbstractActeurFieldset;
use Acteur\Rule\ActeurHDR\ActeurHDRRule;

/**
 * @property \Acteur\Entity\Db\ActeurHDR $object
 * @property \Acteur\Rule\ActeurHDR\ActeurHDRRule $acteurRule
 */
class ActeurHDRFieldset extends AbstractActeurFieldset
{
    public function __construct($name = null, array $options = [])
    {
        parent::__construct($name, $options);

        $this->acteurRule = new ActeurHDRRule();
    }
}