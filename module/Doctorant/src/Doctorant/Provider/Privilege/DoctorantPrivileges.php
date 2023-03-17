<?php

namespace Doctorant\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class DoctorantPrivileges extends Privileges
{
    const DOCTORANT_LISTER_TOUT = 'doctorant-lister-tout'; // todo : existe en bdd mais pas encore exploité
    const DOCTORANT_LISTER_SIEN = 'doctorant-lister-sien'; // todo : existe en bdd mais pas encore exploité
    const DOCTORANT_CONSULTER_TOUT = 'doctorant-consulter-tout'; // todo : existe en bdd mais pas encore exploité
    const DOCTORANT_CONSULTER_SIEN = 'doctorant-consulter-sien'; // todo : existe en bdd mais pas encore exploité
    const DOCTORANT_AFFICHER_EMAIL_CONTACT          = 'doctorant-afficher-email-contact';
    const DOCTORANT_MODIFIER_EMAIL_CONTACT          = 'doctorant-modifier-email-contact';
//    const DOCTORANT_MODIFIER_EMAIL_CONTACT_CONSENT  = 'doctorant-modifier-email-contact-consent';
}