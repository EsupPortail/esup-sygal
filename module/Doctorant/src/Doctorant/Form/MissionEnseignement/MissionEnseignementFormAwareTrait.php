<?php

namespace Doctorant\Form\MissionEnseignement;

trait MissionEnseignementFormAwareTrait {

    private MissionEnseignementForm $missionEnseignementForm;

    public function getMissionEnseignementForm(): MissionEnseignementForm
    {
        return $this->missionEnseignementForm;
    }

    public function setMissionEnseignementForm(MissionEnseignementForm $missionEnseignementForm): void
    {
        $this->missionEnseignementForm = $missionEnseignementForm;
    }

}