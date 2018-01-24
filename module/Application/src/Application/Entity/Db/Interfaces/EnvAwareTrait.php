<?php

namespace Application\Entity\Db\Interfaces;

use Application\Entity\Db\Env;

trait EnvAwareTrait
{
    /**
     * @var Env
     */
    protected $env;

    /**
     * @param Env $env
     * @return static
     */
    public function setEnv(Env $env = null)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * @return Env
     */
    public function getEnv()
    {
        return $this->env;
    }
}