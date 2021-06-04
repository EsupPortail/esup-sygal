<?php

namespace Application\Controller\Rapport;

use Application\Provider\Privilege\RapportPrivileges;

class RapportCsiRechercheController extends RapportRechercheController
{
    protected $privilege_LISTER_TOUT = RapportPrivileges::RAPPORT_CSI_LISTER_TOUT;
    protected $privilege_LISTER_SIEN = RapportPrivileges::RAPPORT_CSI_LISTER_SIEN;
    protected $privilege_TELEVERSER_TOUT = RapportPrivileges::RAPPORT_CSI_TELEVERSER_TOUT;
    protected $privilege_TELEVERSER_SIEN = RapportPrivileges::RAPPORT_CSI_TELEVERSER_SIEN;
    protected $privilege_SUPPRIMER_TOUT = RapportPrivileges::RAPPORT_CSI_SUPPRIMER_TOUT;
    protected $privilege_SUPPRIMER_SIEN = RapportPrivileges::RAPPORT_CSI_SUPPRIMER_SIEN;
    protected $privilege_RECHERCHER_TOUT = RapportPrivileges::RAPPORT_CSI_RECHERCHER_TOUT;
    protected $privilege_RECHERCHER_SIEN = RapportPrivileges::RAPPORT_CSI_RECHERCHER_SIEN;
    protected $privilege_TELECHARGER_TOUT = RapportPrivileges::RAPPORT_CSI_TELECHARGER_TOUT;
    protected $privilege_TELECHARGER_SIEN = RapportPrivileges::RAPPORT_CSI_TELECHARGER_SIEN;
    protected $privilege_TELECHARGER_ZIP = RapportPrivileges::RAPPORT_CSI_TELECHARGER_ZIP;
//    protected $privilege_VALIDER_TOUT = RapportPrivileges::RAPPORT_CSI_VALIDER_TOUT;
//    protected $privilege_VALIDER_SIEN = RapportPrivileges::RAPPORT_CSI_VALIDER_SIEN;
//    protected $privilege_DEVALIDER_TOUT = RapportPrivileges::RAPPORT_CSI_DEVALIDER_TOUT;
//    protected $privilege_DEVALIDER_SIEN = RapportPrivileges::RAPPORT_CSI_DEVALIDER_SIEN;

    protected $routeName = 'rapport-csi';

    protected $title = "Rapports CSI";

}