<?php

namespace Application\Acl;

use Application\Entity\Db\These;
use Application\Entity\Db\WfEtape;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class WfEtapeResource
 *
 * @package Application\Acl
 */
class WfEtapeResource implements ResourceInterface
{
    const RESOURCE_ID = 'WfEtapeResource';

    /**
     * @var WfEtape|string
     */
    private $etape;

    /**
     * @var These
     */
    private $these;

    /**
     * WfEtapeResource constructor.
     *
     * @param WfEtape|string $etape
     * @param These $these
     */
    public function __construct($etape, These $these)
    {
        $this->setEtape($etape);
        $this->setThese($these);
    }

    /**
     * @return WfEtape|string
     */
    public function getEtape()
    {
        return $this->etape;
    }

    /**
     * @param WfEtape $etape
     * @return WfEtapeResource
     */
    public function setEtape($etape)
    {
        $this->etape = $etape;

        return $this;
    }

    /**
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * @param These $these
     * @return WfEtapeResource
     */
    public function setThese($these)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return self::RESOURCE_ID;
    }
}