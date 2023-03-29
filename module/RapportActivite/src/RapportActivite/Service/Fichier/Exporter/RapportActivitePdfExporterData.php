<?php

namespace RapportActivite\Service\Fichier\Exporter;

use RapportActivite\Entity\Db\RapportActivite;
use Structure\Entity\Db\Structure;

class RapportActivitePdfExporterData
{
    public RapportActivite $rapport;

    /** @var \RapportActivite\Entity\RapportActiviteOperationInterface[] */
    public array $operations;

    public array $logosEtablissements;

    public ?Structure $structureCED;
    public string $logoCED;
}