<?php

namespace RapportActivite\Service\Fichier\Exporter;

use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;

class PageValidationExportData
{
    public string $titre;
    public string $specialite;
    public string $doctorant;

    public string $etablissement;
    public string $ecoleDoctorale;
    public string $uniteRecherche;

    public bool $useCOMUE = false;
    public ?string $logoCOMUE = null;

    public string $logoEtablissement;
    public string $logoEcoleDoctorale;
    public string $logoUniteRecherche;

    public ?string $signatureEcoleDoctorale = null;
    public ?string $signatureEcoleDoctoraleAnomalie = null;

    public ?RapportActiviteAvis $mostRecentAvis; // NB : est null pour les validations ante-ModuleRapportActivite.
    public RapportActiviteValidation $validation;

}