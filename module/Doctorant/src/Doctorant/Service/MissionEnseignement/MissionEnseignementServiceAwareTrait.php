<?php

namespace Doctorant\Service\MissionEnseignement;

trait MissionEnseignementServiceAwareTrait
{
    private MissionEnseignementService $missionEnseignementService;

    public function getMissionEnseignementService(): MissionEnseignementService
    {
        return $this->missionEnseignementService;
    }

    public function setMissionEnseignementService(MissionEnseignementService $missionEnseignementService): void
    {
        $this->missionEnseignementService = $missionEnseignementService;
    }


}