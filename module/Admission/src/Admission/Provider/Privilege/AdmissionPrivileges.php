<?php

namespace Admission\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class AdmissionPrivileges extends Privileges {

    const ADMISSION_LISTER      = 'admission_admission-lister';
    const ADMISSION_AFFICHER   = 'admission_admission-afficher';
    const ADMISSION_AJOUTER    = 'admission_admission-ajouter';
    const ADMISSION_MODIFIER   = 'admission_admission-modifier';
    const ADMISSION_HISTORISER = 'admission_admission-historiser';
    const ADMISSION_SUPPRIMER  = 'admission_admission-supprimer';
    const ADMISSION_VERIFIER  = 'admission_admission-verifier';
}