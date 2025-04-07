<?php

namespace Indicateur\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class IndicateurPrivileges extends Privileges
{
    public const INDICATEUR_CONSULTATION                  = 'indicateur-consultation';
    public const INDICATEUR_EXPORTATION                   = 'indicateur-exportation';
    public const INDICATEUR_RAFRAICHISSEMENT              = 'indicateur-rafraichissement';
    public const INDICATEUR_EDITION                       = 'indicateur-edition';
    public const INDICATEUR_STATISTIQUE                   = 'indicateur-consultation-statistique';

}