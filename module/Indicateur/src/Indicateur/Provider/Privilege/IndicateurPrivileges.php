<?php

namespace Indicateur\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class IndicateurPrivileges extends Privileges
{
    const INDICATEUR_CONSULTATION                  = 'indicateur-consultation';
    const INDICATEUR_EXPORTATION                   = 'indicateur-exportation';
    const INDICATEUR_RAFRAICHISSEMENT              = 'indicateur-rafraichissement';
    const INDICATEUR_EDITION                       = 'indicateur-edition';
    const INDICATEUR_STATISTIQUE                   = 'indicateur-consultation-statistique';

}