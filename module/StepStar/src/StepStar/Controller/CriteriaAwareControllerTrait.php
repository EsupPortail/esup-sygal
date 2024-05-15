<?php

namespace StepStar\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

trait CriteriaAwareControllerTrait
{
    private ?string $these;
    private ?string $etat;
    private ?bool $dateSoutenanceNull;
    private ?string $dateSoutenanceMin;
    private ?string $dateSoutenanceMax;
    private ?string $etablissement;
    private ?bool $force;
    private ?string $tag;
    private ?bool $clean;

    protected function loadCriteriaFromControllerParams(AbstractActionController $controller): void
    {
        $this->these = $controller->params()->fromRoute('these'); // ex : '12345' ou '12345,12346'
        $this->etat = $controller->params()->fromRoute('etat'); // ex : 'E' ou 'E,S'
        $this->dateSoutenanceNull = (bool) $controller->params()->fromRoute('date-soutenance-null');
        $this->dateSoutenanceMin = $controller->params()->fromRoute('date-soutenance-min'); // ex : '2022-03-11' ou 'P6M'
        $this->dateSoutenanceMax = $controller->params()->fromRoute('date-soutenance-max'); // ex : '2022-03-11' ou 'P6M'
        $this->etablissement = $controller->params()->fromRoute('etablissement'); // ex : 'UCN' ou 'UCN,URN'
        $this->force = (bool) $this->params()->fromRoute('force');
        $this->tag = $controller->params()->fromRoute('tag');
        $this->clean = (bool) $this->params()->fromRoute('clean');
    }

    protected function getCriteriaAsArray(): array
    {
        return array_filter([
            'these' => $this->these,
            'etat' => $this->etat,
            'dateSoutenanceNull' => $this->dateSoutenanceNull,
            'dateSoutenanceMin' => $this->dateSoutenanceMin,
            'dateSoutenanceMax' => $this->dateSoutenanceMax,
            'etablissement' => $this->etablissement,
            'force' => $this->force,
            'tag' => $this->tag,
            'clean' => $this->clean,
        ]);
    }
}