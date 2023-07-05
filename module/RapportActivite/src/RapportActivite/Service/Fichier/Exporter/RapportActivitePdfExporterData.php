<?php

namespace RapportActivite\Service\Fichier\Exporter;

use Application\Entity\AnneeUniv;
use RapportActivite\Entity\Db\RapportActivite;

class RapportActivitePdfExporterData
{
    public RapportActivite $rapport;

    /** @var \RapportActivite\Entity\RapportActiviteOperationInterface[] */
    public array $operations;

    public AnneeUniv $anneeUnivCourante;

    public array $logosEtablissements;
    public ?string $logoCED = null;
}