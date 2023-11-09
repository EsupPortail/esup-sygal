<?php

namespace Admission\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class AdmissionPrivileges extends Privileges {

    const ADMISSION_LISTER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-lister-tous-dossiers-admission';
    const ADMISSION_LISTER_SON_DOSSIER_ADMISSION  = 'admission-admission-lister-son-dossier-admission';
    const ADMISSION_AFFICHER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-afficher-tous-dossiers-admission';
    const ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION = 'admission-admission-afficher-son-dossier-admission';
    const ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-modifier-tous-dossiers-admission';
    const ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION     = 'admission-admission-modifier-son-dossier-admission';

    const ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-supprimer-tous-dossiers-admission';
    const ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION     = 'admission-admission-supprimer-son-dossier-admission';


    const ADMISSION_HISTORISER = 'admission-admission-historiser';
    const ADMISSION_VERIFIER  = 'admission-admission-verifier';
}