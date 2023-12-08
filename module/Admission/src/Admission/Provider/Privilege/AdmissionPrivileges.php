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

    //validation
    const ADMISSION_VALIDER_TOUT = 'admission-admission-valider-tout';
    const ADMISSION_VALIDER_SIEN = 'admission-admission-valider-sien';
    const ADMISSION_DEVALIDER_TOUT = 'admission-admission-devalider-tout';
    const ADMISSION_DEVALIDER_SIEN = 'admission-admission-devalider-sien';
    const ADMISSION_HISTORISER = 'admission-admission-historiser';
    const ADMISSION_VERIFIER  = 'admission-admission-verifier';

    //gestion des documents
    const ADMISSION_TELEVERSER_TOUT_DOCUMENT = 'admission-admission-televerser-tout-document';
    const ADMISSION_TELEVERSER_SON_DOCUMENT = 'admission-admission-televerser-son-document';
    const ADMISSION_SUPPRIMER_TOUT_DOCUMENT = 'admission-admission-supprimer-tout-document';
    const ADMISSION_SUPPRIMER_SON_DOCUMENT = 'admission-admission-supprimer-son-document';
    const ADMISSION_TELECHARGER_TOUT_DOCUMENT = 'admission-admission-telecharger-tout-document';
    const ADMISSION_TELECHARGER_SON_DOCUMENT = 'admission-admission-telecharger-son-document';

    //notification
    const ADMISSION_NOTIFIER_GESTIONNAIRES  = 'admission-admission-notifier-gestionnaires';
    const ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES  = 'admission-admission-commentaires-ajoutes';
}