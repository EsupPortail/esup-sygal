<?php

namespace ComiteSuivi\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class ComiteSuiviPrivileges extends Privileges
{
    const COMITESUIVI_AFFICHER      = 'CS_comite-ComiteSuivi_afficher';
    const COMITESUIVI_AJOUTER       = 'CS_comite-ComiteSuivi_ajouter';
    const COMITESUIVI_MODIFIER      = 'CS_comite-ComiteSuivi_modifier';
    const COMITESUIVI_HISTORISER    = 'CS_comite-ComiteSuivi_historiser';
    const COMITESUIVI_SUPPRIMER     = 'CS_comite-ComiteSuivi_supprimer';
    const COMITESUIVI_VALIDER       = 'CS_comite-ComiteSuivi_valider';
}