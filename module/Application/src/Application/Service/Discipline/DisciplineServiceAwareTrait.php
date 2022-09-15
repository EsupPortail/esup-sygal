<?php

namespace Application\Service\Discipline;

trait DisciplineServiceAwareTrait {

    private DisciplineService $disciplineService;

    /**
     * @return DisciplineService
     */
    public function getDisciplineService(): DisciplineService
    {
        return $this->disciplineService;
    }

    /**
     * @param DisciplineService $disciplineService
     */
    public function setDisciplineService(DisciplineService $disciplineService): void
    {
        $this->disciplineService = $disciplineService;
    }


}