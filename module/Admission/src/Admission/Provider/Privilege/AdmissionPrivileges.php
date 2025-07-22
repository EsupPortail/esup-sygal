<?php

namespace Admission\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class AdmissionPrivileges extends Privileges {
    public const ADMISSION_CONFIGURER_MODULE  = 'admission-admission-configurer-module';
    public const ADMISSION_LISTER_MES_DOSSIERS_ADMISSION  = 'admission-admission-lister-mes-dossiers-admission';
    public const ADMISSION_INITIALISER_ADMISSION  = 'admission-admission-initialiser-son-dossier-admission';
    public const ADMISSION_RECHERCHER_DOSSIERS_ADMISSION  = 'admission-admission-rechercher-dossiers-admission';
    public const ADMISSION_AFFICHER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-afficher-tous-dossiers-admission';
    public const ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION = 'admission-admission-afficher-son-dossier-admission';
    public const ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION_DANS_LISTE = 'admission-admission-afficher-son-dossier-admission-dans-liste';
    public const ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-modifier-tous-dossiers-admission';
    public const ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION     = 'admission-admission-modifier-son-dossier-admission';
    public const ADMISSION_MODIFIER_DOSSIER_ADMISSION_GESTION     = 'admission-admission-modifier-dossier-admission-gestion';
    public const ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION  = 'admission-admission-supprimer-tous-dossiers-admission';
    public const ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION     = 'admission-admission-supprimer-son-dossier-admission';
    public const ADMISSION_GENERER_EXPORT_ADMISSIONS  = 'admission-admission-generer-export-admissions';
    public const ADMISSION_AJOUTER_DONNEES_EXPORT  = 'admission-admission-ajouter-donnees-export';
    public const ADMISSION_GENERER_RECAPITULATIF     = 'admission-admission-generer-recapitulatif';

    //validation
    public const ADMISSION_VALIDER_TOUT = 'admission-admission-valider-tout';
    public const ADMISSION_VALIDER_SIEN = 'admission-admission-valider-sien';
    public const ADMISSION_DEVALIDER_TOUT = 'admission-admission-devalider-tout';
    public const ADMISSION_DEVALIDER_SIEN = 'admission-admission-devalider-sien';
    public const ADMISSION_HISTORISER = 'admission-admission-historiser';
    public const ADMISSION_VERIFIER  = 'admission-admission-verifier';
    public const ADMISSION_ACCEDER_COMMENTAIRES  = 'admission-admission-acceder-commentaires';

    //avis
    public const ADMISSION_AJOUTER_AVIS_TOUT = 'admission-admission-ajouter-avis-tout';
    public const ADMISSION_AJOUTER_AVIS_SIEN = 'admission-admission-ajouter-avis-sien';
    public const ADMISSION_MODIFIER_AVIS_TOUT = 'admission-admission-modifier-avis-tout';
    public const ADMISSION_MODIFIER_AVIS_SIEN = 'admission-admission-modifier-avis-sien';
    public const ADMISSION_SUPPRIMER_AVIS_TOUT = 'admission-admission-supprimer-avis-tout';
    public const ADMISSION_SUPPRIMER_AVIS_SIEN = 'admission-admission-supprimer-avis-sien';

    //gestion des documents
    public const ADMISSION_TELEVERSER_TOUT_DOCUMENT = 'admission-admission-televerser-tout-document';
    public const ADMISSION_TELEVERSER_SON_DOCUMENT = 'admission-admission-televerser-son-document';
    public const ADMISSION_SUPPRIMER_TOUT_DOCUMENT = 'admission-admission-supprimer-tout-document';
    public const ADMISSION_SUPPRIMER_SON_DOCUMENT = 'admission-admission-supprimer-son-document';
    public const ADMISSION_TELECHARGER_TOUT_DOCUMENT = 'admission-admission-telecharger-tout-document';
    public const ADMISSION_TELECHARGER_SON_DOCUMENT = 'admission-admission-telecharger-son-document';
    public const ADMISSION_GERER_RECAPITULATIF_DOSSIER = 'admission-admission-gerer-recapitulatif-signe-dossier';
    public const ADMISSION_ACCEDER_RECAPITULATIF_DOSSIER = 'admission-admission-acceder-recapitulatif-signe-dossier';

    //notification
    public const ADMISSION_NOTIFIER_DOSSIER_INCOMPLET  = 'admission-admission-notifier-dossier-incomplet';

    //convention de formation doctorale
    public const ADMISSION_CONVENTION_FORMATION_MODIFIER  = 'admission-admission-convention-formation-modifier';
    public const ADMISSION_CONVENTION_FORMATION_VISUALISER  = 'admission-admission-convention-formation-visualiser';
    public const ADMISSION_CONVENTION_FORMATION_GENERER  = 'admission-admission-convention-formation-generer';
}