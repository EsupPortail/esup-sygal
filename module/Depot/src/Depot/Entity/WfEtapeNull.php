<?php

namespace Depot\Entity;

use Depot\Entity\Db\WfEtape;

/**
 * Null Object Pattern.
 *
 * @author Unicaen
 */
class WfEtapeNull extends WfEtape
{
    /**
     * @var WfEtapeNull
     */
    static $instance;

    /**
     * @return static
     */
    static public function inst()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * WfEtapeNull constructor.
     */
    protected function __construct()
    {
        $this->setCode('ETAPE_NULL');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Étape Null";
    }
}