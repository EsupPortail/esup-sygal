<?php

namespace Individu\Service\IndividuRoleEtablissement;

trait IndividuRoleEtablissementServiceAwareTrait
{
    protected IndividuRoleEtablissementService $individuRoleEtablissementService;

    public function getIndividuRoleEtablissementService(): IndividuRoleEtablissementService
    {
        return $this->individuRoleEtablissementService;
    }

    public function setIndividuRoleEtablissementService(IndividuRoleEtablissementService $individuRoleEtablissementService): void
    {
        $this->individuRoleEtablissementService = $individuRoleEtablissementService;
    }
}