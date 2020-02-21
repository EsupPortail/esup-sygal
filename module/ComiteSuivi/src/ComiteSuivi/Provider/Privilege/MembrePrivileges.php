<?php

namespace ComiteSuivi\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class MembrePrivileges extends Privileges
{
    const MEMBRE_AFFICHER      = 'CS_membre-ComiteMembre_afficher';
    const MEMBRE_AJOUTER       = 'CS_membre-ComiteMembre_ajouter';
    const MEMBRE_MODIFIER      = 'CS_membre-ComiteMembre_modifier';
    const MEMBRE_HISTORISER    = 'CS_membre-ComiteMembre_historiser';
    const MEMBRE_SUPPRIMER     = 'CS_membre-ComiteMembre_supprimer';
    const MEMBRE_LIER          = 'CS_membre-ComiteMembre_lier';
}