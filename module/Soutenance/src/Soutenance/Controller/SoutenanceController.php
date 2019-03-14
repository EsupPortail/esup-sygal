<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * @method boolean isAllowed($resource, $privilege = null)
 */

class SoutenanceController extends AbstractActionController {
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;

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

