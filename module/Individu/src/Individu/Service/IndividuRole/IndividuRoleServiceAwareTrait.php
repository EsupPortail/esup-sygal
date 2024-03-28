<?php

namespace Individu\Service\IndividuRole;

trait IndividuRoleServiceAwareTrait
{
    protected IndividuRoleService $individuRoleService;

    public function getIndividuRoleService(): IndividuRoleService
    {
        return $this->individuRoleService;
    }

    public function setIndividuRoleService(IndividuRoleService $individuRoleService): void
    {
        $this->individuRoleService = $individuRoleService;
    }
}