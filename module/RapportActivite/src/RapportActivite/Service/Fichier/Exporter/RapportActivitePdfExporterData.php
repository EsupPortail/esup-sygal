<?php

namespace RapportActivite\Service\Fichier\Exporter;

use RapportActivite\Entity\Db\RapportActivite;
use Structure\Entity\Db\Structure;

class RapportActivitePdfExporterData
{
    public RapportActivite $rapport;

    /** @var \RapportActivite\Entity\RapportActiviteOperationInterface[] */
    public array $operations;

    public bool $useCOMUE = false;
    public ?string $logoCOMUE = null;

    public string $logoEtablissement;
    public string $logoEcoleDoctorale;
    public string $logoUniteRecherche;

    public ?Structure $structureCED;
    public string $logoCED;
}