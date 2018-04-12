<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Form\EcoleDoctoraleForm;
use Application\RouteMatch;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use Zend\View\Model\ViewModel;

class SubstitutionController extends AbstractController
{
    use EtablissementServiceAwareTrait;
    public function indexAction()
    {
        return new ViewModel();
    }

    public function selectionAction()
    {

        $etablissements = $this->etablissementService->getEtablissements();
        $etablissement = $this->etablissementService->getEtablissementById(2);
        $selection = [
            $this->etablissementService->getEtablissementById(2),
            $this->etablissementService->getEtablissementById(3),
            $this->etablissementService->getEtablissementById(4)
        ];
        return new ViewModel([
            'nouvelEtablissement' => $etablissement,
            'selectedEtablissements' => $selection,
            'etablissements' => $etablissements,
        ]);
    }
}