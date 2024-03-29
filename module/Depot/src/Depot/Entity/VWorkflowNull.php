<?php

namespace Depot\Entity;

use Depot\Entity\Db\VWorkflow;
use These\Entity\Db\These;

/**
 * Null Object Pattern.
 *
 * @author Unicaen
 */
class VWorkflowNull extends VWorkflow
{
    /**
     * @var VWorkflowNull
     */
    static $instance;

    /**
     * @param These $these
     * @return static
     */
    static public function inst(These $these)
    {
        if (null === static::$instance) {
            static::$instance = new static($these);
        }

        return static::$instance;
    }

    /**
     * VWorkflowNull constructor.
     *
     * @param These $these
     */
    protected function __construct(These $these)
    {
        $this->setThese($these);
        $this->setEtape(WfEtapeNull::inst());
    }
}