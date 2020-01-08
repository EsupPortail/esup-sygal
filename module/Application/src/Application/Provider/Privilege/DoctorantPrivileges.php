<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 *
 * @author UnicaenCode
 */
class DoctorantPrivileges extends Privileges
{
    const DOCTORANT_MODIFICATION_PERSOPASS          = 'doctorant-modification-persopass';
    const DOCTORANT_AFFICHER_EMAIL_CONTACT          = 'doctorant-afficher-mail-contact';
}