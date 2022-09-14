<?php

namespace These\Entity\Db\Interfaces;

use These\Entity\Db\These;

trait TheseAwareTrait
{
    /**
     * @var These
     */
    protected $these;

    /**
     * @param These $these
     * @return static
     */
    public function setThese(These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }
}