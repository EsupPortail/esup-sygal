<?php

namespace Formation\Service\Presence;

trait PresenceServiceAwareTrait
{
    /** @var PresenceService */
    private $presenceService;

    /**
     * @return PresenceService
     */
    public function getPresenceService(): PresenceService
    {
        return $this->presenceService;
    }

    /**
     * @param PresenceService $presenceService
     * @return PresenceService
     */
    public function setPresenceService(PresenceService $presenceService): PresenceService
    {
        $this->presenceService = $presenceService;
        return $this->presenceService;
    }

}