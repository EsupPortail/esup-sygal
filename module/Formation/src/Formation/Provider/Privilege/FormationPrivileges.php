<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class FormationPrivileges extends Privileges {

    const FORMATION_INDEX      = 'formation_formation-index';
    const FORMATION_AFFICHER   = 'formation_formation-afficher';
    const FORMATION_AJOUTER    = 'formation_formation-ajouter';
    const FORMATION_MODIFIER   = 'formation_formation-modifier';
    const FORMATION_HISTORISER = 'formation_formation-historiser';
    const FORMATION_SUPPRIMER  = 'formation_formation-supprimer';

    /*************** Formation Externe **************/
    const FORMATION_FORMATIONEXTERNE_AJOUTER    = 'formation_formationexterne-ajouter';
    const FORMATION_FORMATIONEXTERNE_MODIFIER   = 'formation_formationexterne-modifier';
    const FORMATION_FORMATIONEXTERNE_HISTORISER = 'formation_formationexterne-historiser';
    const FORMATION_FORMATIONEXTERNE_SUPPRIMER  = 'formation_formationexterne-supprimer';
    const FORMATION_FORMATIONEXTERNE_TELECHARGER_ATTESTATION  = 'formation_formationexterne-telecharger-attestation';
    const FORMATION_FORMATIONEXTERNE_GERER_VALIDATION  = 'formation_formationexterne-gerer-validation';
    const FORMATION_FORMATIONEXTERNE_NOTIFIER_FORMATION_AJOUTEE  = 'formation_formationexterne-notifier-formation-ajoutee';

    const FORMATION_EXPORTER_XLS  = 'formation_formation-exporter-xls';
}