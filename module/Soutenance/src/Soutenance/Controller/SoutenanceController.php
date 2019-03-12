<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * @method boolean isAllowed($resource, $privilege = null)
 */

class SoutenanceController extends AbstractActionController {
    use TheseServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use UserContextServiceAwareTrait;

    public function indexAction()
    {
        /** @var These[] $theses */
        $theses = [];
        $individu = $this->userContextService->getIdentityIndividu();
        $role = $this->userContextService->getSelectedIdentityRole();

        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
                $theses = $this->getTheseService()->getRepository()->fetchThesesByDoctorantAsIndividu($individu);
                break;
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
                $theses = $this->getTheseService()->getRepository()->fetchThesesByEncadrant($individu);
                break;
//            case Role::CODE_RAPPORTEUR_JURY :
//            case Role::CODE_RAPPORTEUR_ABSENT :
//                break;
            case Role::CODE_ADMIN_TECH :
            case Role::CODE_OBSERVATEUR :
            case Role::CODE_BDD :
            case Role::CODE_UR :
            case Role::CODE_ED :
                $this->redirect()->toRoute('soutenance/index-structure', [], [], true);
                break;
        }

        return new ViewModel([
            'theses'            => $theses,
        ]);
    }

    public function indexStructureAction()
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        $propositions = $this->getPropositionService()->getPropositionsByRole($role);

        return new ViewModel([
            'propositions' => $propositions,
        ]);
    }

    public function avancementAction()
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Acteur[] $directeurs */
        $directeurs = $these->getEncadrements(false);

        /** @var Membre[] $rapporteurs */
        $rapporteurs = ($proposition)?$proposition->getRapporteurs():[];

        return new ViewModel([
            'these'             => $these,
            'proposition'       => $proposition,
            'jury'              => $this->getPropositionService()->juryOk($proposition),
            'validations'       => ($proposition)?$this->getPropositionService()->getValidationSoutenance($these):[],
            'directeurs'        => $directeurs,
            'rapporteurs'       => $rapporteurs,
        ]);
    }

}

