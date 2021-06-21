<?php

namespace Application\Controller\Rapport;

use Application\Provider\Privilege\RapportPrivileges;

class RapportCsiController extends RapportController
{
    protected $routeName = 'rapport-csi';

    protected $privilege_TELEVERSER_TOUT = RapportPrivileges::RAPPORT_CSI_TELEVERSER_TOUT;
    protected $privilege_TELEVERSER_SIEN = RapportPrivileges::RAPPORT_CSI_TELEVERSER_SIEN;
    protected $privilege_SUPPRIMER_TOUT = RapportPrivileges::RAPPORT_CSI_SUPPRIMER_TOUT;
    protected $privilege_SUPPRIMER_SIEN = RapportPrivileges::RAPPORT_CSI_SUPPRIMER_SIEN;
    protected $privilege_TELECHARGER_TOUT = RapportPrivileges::RAPPORT_CSI_TELECHARGER_TOUT;
    protected $privilege_TELECHARGER_SIEN = RapportPrivileges::RAPPORT_CSI_TELECHARGER_SIEN;
//    protected $privilege_VALIDER_TOUT = RapportPrivileges::RAPPORT_CSI_VALIDER_TOUT;
//    protected $privilege_VALIDER_SIEN = RapportPrivileges::RAPPORT_CSI_VALIDER_SIEN;
//    protected $privilege_DEVALIDER_TOUT = RapportPrivileges::RAPPORT_CSI_DEVALIDER_TOUT;
//    protected $privilege_DEVALIDER_SIEN = RapportPrivileges::RAPPORT_CSI_DEVALIDER_SIEN;
}
