<?php

namespace Soutenance\Service\EngagementImpartialite;

trait EngagementImpartialiteServiceAwareTrait {

    /** @var EngagementImpartialiteService */
    private $engagementImpartialiteService;

    /**
     * @return EngagementImpartialiteService
     */
    public function getEngagementImpartialiteService()
    {
        return $this->engagementImpartialiteService;
    }

    /**
     * @param EngagementImpartialiteService $engagementImpartialiteService
     * @return EngagementImpartialiteService
     */
    public function setEngagementImpartialiteService($engagementImpartialiteService)
    {
        $this->engagementImpartialiteService = $engagementImpartialiteService;
        return $this->engagementImpartialiteService;
    }
}