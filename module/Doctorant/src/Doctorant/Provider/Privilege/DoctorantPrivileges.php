<?php

namespace Doctorant\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class DoctorantPrivileges extends Privileges
{
    const DOCTORANT_LISTER_TOUT = 'doctorant-lister-tous';
    const DOCTORANT_LISTER_SIEN = 'doctorant-lister-sien';
    const DOCTORANT_CONSULTER_TOUS = 'doctorant-consulter-tous';
    const DOCTORANT_CONSULTER_SIEN = 'doctorant-consulter-sien';
    const DOCTORANT_MODIFIER_TOUS = 'doctorant-modifier-tous';
    const DOCTORANT_MODIFIER_SIEN = 'doctorant-modifier-sien';
    const DOCTORANT_AFFICHER_EMAIL_CONTACT          = 'doctorant-afficher-email-contact';
    const DOCTORANT_MODIFIER_EMAIL_CONTACT          = 'doctorant-modifier-email-contact';
//    const DOCTORANT_MODIFIER_EMAIL_CONTACT_CONSENT  = 'doctorant-modifier-email-contact-consent';
}