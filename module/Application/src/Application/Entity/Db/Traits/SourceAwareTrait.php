<?php

namespace Application\Entity\Db\Traits;

use UnicaenImport\Entity\Db\Source;

trait SourceAwareTrait
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @param Source $source
     * @return self
     */
    public function setSource(Source $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }
}