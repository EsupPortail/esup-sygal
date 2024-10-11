<?php

namespace Admission\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class AdmissionPrivileges extends Privileges {
    const ADMISSION_LISTER_MES_DOSSIERS_ADMISSION  = 'admission-admission-lister-mes-dossiers-admission';
    const ADMISSION_INITIALISER_ADMISSION  = 'admission-admission-initialiser-son-dossier-admission';
    const ADMISSION_RECHERCHER_DOSSIERS_ADMISSION  = 'admission-admission-rechercher-dossiers-admission';
    const ADMISSION_AFFICHER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-afficher-tous-dossiers-admission';
    const ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION = 'admission-admission-afficher-son-dossier-admission';
    const ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION_DANS_LISTE = 'admission-admission-afficher-son-dossier-admission-dans-liste';
    const ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-modifier-tous-dossiers-admission';
    const ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION     = 'admission-admission-modifier-son-dossier-admission';
    const ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-supprimer-tous-dossiers-admission';
    const ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION     = 'admission-admission-supprimer-son-dossier-admission';
    const ADMISSION_GENERER_EXPORT_ADMISSIONS  = 'admission-admission-generer-export-admissions';
    const ADMISSION_AJOUTER_DONNEES_EXPORT  = 'admission-admission-ajouter-donnees-export';
    const ADMISSION_GENERER_RECAPITULATIF     = 'admission-admission-generer-recapitulatif';

    //validation
    const ADMISSION_VALIDER_TOUT = 'admission-admission-valider-tout';
    const ADMISSION_VALIDER_SIEN = 'admission-admission-valider-sien';
    const ADMISSION_DEVALIDER_TOUT = 'admission-admission-devalider-tout';
    const ADMISSION_DEVALIDER_SIEN = 'admission-admission-devalider-sien';
    const ADMISSION_HISTORISER = 'admission-admission-historiser';
    const ADMISSION_VERIFIER  = 'admission-admission-verifier';
    const ADMISSION_ACCEDER_COMMENTAIRES  = 'admission-admission-acceder-commentaires';

    //avis
    const ADMISSION_AJOUTER_AVIS_TOUT = 'admission-admission-ajouter-avis-tout';
    const ADMISSION_AJOUTER_AVIS_SIEN = 'admission-admission-ajouter-avis-sien';
    const ADMISSION_MODIFIER_AVIS_TOUT = 'admission-admission-modifier-avis-tout';
    const ADMISSION_MODIFIER_AVIS_SIEN = 'admission-admission-modifier-avis-sien';
    const ADMISSION_SUPPRIMER_AVIS_TOUT = 'admission-admission-supprimer-avis-tout';
    const ADMISSION_SUPPRIMER_AVIS_SIEN = 'admission-admission-supprimer-avis-sien';

    //gestion des documents
    const ADMISSION_TELEVERSER_TOUT_DOCUMENT = 'admission-admission-televerser-tout-document';
    const ADMISSION_TELEVERSER_SON_DOCUMENT = 'admission-admission-televerser-son-document';
    const ADMISSION_SUPPRIMER_TOUT_DOCUMENT = 'admission-admission-supprimer-tout-document';
    const ADMISSION_SUPPRIMER_SON_DOCUMENT = 'admission-admission-supprimer-son-document';
    const ADMISSION_TELECHARGER_TOUT_DOCUMENT = 'admission-admission-telecharger-tout-document';
    const ADMISSION_TELECHARGER_SON_DOCUMENT = 'admission-admission-telecharger-son-document';
    const ADMISSION_GERER_RECAPITULATIF_DOSSIER = 'admission-admission-gerer-recapitulatif-signe-dossier';
    const ADMISSION_ACCEDER_RECAPITULATIF_DOSSIER = 'admission-admission-acceder-recapitulatif-signe-dossier';

    //notification
    const ADMISSION_NOTIFIER_DOSSIER_INCOMPLET  = 'admission-admission-notifier-dossier-incomplet';

    //convention de formation doctorale
    const ADMISSION_CONVENTION_FORMATION_MODIFIER  = 'admission-admission-convention-formation-modifier';
    const ADMISSION_CONVENTION_FORMATION_VISUALISER  = 'admission-admission-convention-formation-visualiser';
    const ADMISSION_CONVENTION_FORMATION_GENERER  = 'admission-admission-convention-formation-generer';
}