<?php

namespace Application\Controller\Rapport;

use Application\Entity\Db\Rapport;
use Application\Provider\Privilege\RapportPrivileges;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\AutorisationInscription\AutorisationInscriptionServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use ComiteSuiviIndividuel\Service\Membre\MembreServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;

class RapportCsiController extends RapportController
{
    use MembreServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use SourceServiceAwareTrait;
    use TheseServiceAwareTrait;
    use AutorisationInscriptionServiceAwareTrait;


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

    /**
     * @return Response|ViewModel
     */
    public function consulterAction()
    {
        $this->these = $this->requestedThese();
        $this->fetchRapportsTeleverses();

        $membres = $this->getMembreService()->getMembresByThese($this->these);

        // gestion d'une éventuelle requête POST d'ajout d'un rapport
        $result = $this->ajouterAction();
        if ($result instanceof Response) {
            return $result;
        }

        $autorisationsInscription = $this->fetchAutorisationsInscriptionParThese($this->these);
        foreach ($this->rapportsTeleverses as $rapport) {
            $matchedAutorisation = null;
            foreach ($autorisationsInscription as $autorisationInscription) {
                if ($rapport === $autorisationInscription->getRapport()) {
                    $matchedAutorisation = $autorisationInscription;
                    break;
                }
            }
            $rapportsAvecAutorisationInscription[] = [
                'rapport' => $rapport,
                'autorisationInscription' => $matchedAutorisation
            ];
        }

        return new ViewModel([
            'rapports' => $rapportsAvecAutorisationInscription ?? $this->rapportsTeleverses,
            'autorisationsInscription' => $this->fetchAutorisationsInscriptionParThese($this->these),
            'these' => $this->these,
            'form' => $this->form,
            'isTeleversementPossible' => $this->isTeleversementPossible(),
            'membres' => $membres,

            'typeValidation' => $this->typeValidation,
            'routeName' => $this->routeName,
            'privilege_LISTER_TOUT' => $this->privilege_LISTER_TOUT,
            'privilege_LISTER_SIEN' => $this->privilege_LISTER_SIEN,
            'privilege_TELEVERSER_TOUT' => $this->privilege_TELEVERSER_TOUT,
            'privilege_TELEVERSER_SIEN' => $this->privilege_TELEVERSER_SIEN,
            'privilege_SUPPRIMER_TOUT' => $this->privilege_SUPPRIMER_TOUT,
            'privilege_SUPPRIMER_SIEN' => $this->privilege_SUPPRIMER_SIEN,
            'privilege_RECHERCHER_TOUT' => $this->privilege_RECHERCHER_TOUT,
            'privilege_RECHERCHER_SIEN' => $this->privilege_RECHERCHER_SIEN,
            'privilege_TELECHARGER_TOUT' => $this->privilege_TELECHARGER_TOUT,
            'privilege_TELECHARGER_SIEN' => $this->privilege_TELECHARGER_SIEN,
            'privilege_TELECHARGER_ZIP' => $this->privilege_TELECHARGER_ZIP,
            'privilege_VALIDER_TOUT' => $this->privilege_VALIDER_TOUT,
            'privilege_VALIDER_SIEN' => $this->privilege_VALIDER_SIEN,
            'privilege_DEVALIDER_TOUT' => $this->privilege_DEVALIDER_TOUT,
            'privilege_DEVALIDER_SIEN' => $this->privilege_DEVALIDER_SIEN,
        ]);
    }

    private function fetchAutorisationsInscriptionParThese(These $these): array
    {
        return $this->autorisationInscriptionService->findAutorisationsInscriptionParThese($these);
    }
}
