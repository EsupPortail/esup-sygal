<?php

namespace RapportActivite\Service\Fichier\Exporter;

use RapportActivite\Entity\Db\RapportActivite;

class RapportActivitePdfExporterData
{
    public RapportActivite $rapport;

    /** @var \RapportActivite\Entity\RapportActiviteOperationInterface[] */
    public array $operations;

    public array $logosEtablissements;
    public ?string $logoCED = null;
}