<?php

namespace ComiteSuivi\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class CompteRenduPrivileges extends Privileges
{
    const COMPTERENDU_AFFICHER      = 'CS_compterendu-ComiteRapport_afficher';
    const COMPTERENDU_AJOUTER       = 'CS_compterendu-ComiteRapport_ajouter';
    const COMPTERENDU_MODIFIER      = 'CS_compterendu-ComiteRapport_modifier';
    const COMPTERENDU_HISTORISER    = 'CS_compterendu-ComiteRapport_historiser';
    const COMPTERENDU_SUPPRIMER     = 'CS_compterendu-ComiteRapport_supprimer';
}