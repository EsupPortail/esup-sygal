<?php

namespace Application\Controller\Rapport;

use Application\Provider\Privilege\RapportPrivileges;

class RapportMiparcoursController extends RapportController
{
    protected $routeName = 'rapport-miparcours';

    protected $privilege_TELEVERSER_TOUT = RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_TOUT;
    protected $privilege_TELEVERSER_SIEN = RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_SIEN;
    protected $privilege_SUPPRIMER_TOUT = RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_TOUT;
    protected $privilege_SUPPRIMER_SIEN = RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_SIEN;
    protected $privilege_TELECHARGER_TOUT = RapportPrivileges::RAPPORT_MIPARCOURS_TELECHARGER_TOUT;
    protected $privilege_TELECHARGER_SIEN = RapportPrivileges::RAPPORT_MIPARCOURS_TELECHARGER_SIEN;
//    protected $privilege_VALIDER_TOUT = RapportPrivileges::RAPPORT_MIPARCOURS_VALIDER_TOUT;
//    protected $privilege_VALIDER_SIEN = RapportPrivileges::RAPPORT_MIPARCOURS_VALIDER_SIEN;
//    protected $privilege_DEVALIDER_TOUT = RapportPrivileges::RAPPORT_MIPARCOURS_DEVALIDER_TOUT;
//    protected $privilege_DEVALIDER_SIEN = RapportPrivileges::RAPPORT_MIPARCOURS_DEVALIDER_SIEN;
}
