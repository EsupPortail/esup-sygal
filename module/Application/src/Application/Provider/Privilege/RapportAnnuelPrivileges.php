<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class RapportAnnuelPrivileges extends Privileges
{
    const RAPPORT_ANNUEL_CONSULTER = 'rapport-annuel-consulter';
    const RAPPORT_ANNUEL_TELEVERSER = 'rapport-annuel-televerser';
    const RAPPORT_ANNUEL_SUPPRIMER = 'rapport-annuel-supprimer';
    const RAPPORT_ANNUEL_TELECHARGER = 'rapport-annuel-telecharger';
    const RAPPORT_ANNUEL_TELECHARGER_ZIP = 'rapport-annuel-telecharger-zip';
    const RAPPORT_ANNUEL_RECHERCHER = 'rapport-annuel-rechercher';
}
