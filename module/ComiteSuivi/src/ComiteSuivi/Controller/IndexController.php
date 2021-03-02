<?php

namespace ComiteSuivi\Controller;

use Application\Entity\Db\Role;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\Membre;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {
    use ComiteSuiviServiceAwareTrait;
    use DateTimeTrait;
    use TheseServiceAwareTrait;
    use UserContextServiceAwareTrait;

    public function indexAction()
    {
        /** @var Role $role */
        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/index/index-non-autorise');
        $comites = [];

        switch ($role->getCode()) {
            case Role::CODE_ADMIN_TECH :
                $comites = $this->getComiteSuiviService()->getComitesSuivisByAnneeScolaire($this->getAnneeScolaire());
                $vm->setTemplate('comite-suivi/index/index-comites');
                break;
            case Role::CODE_ED :
                $ecole = $role->getStructure()->getEcoleDoctorale();
                $comites = $this->getComiteSuiviService()->getComitesSuivisByEcoleAndAnneeScolaire($ecole, $this->getAnneeScolaire());
                $vm->setTemplate('comite-suivi/index/index-comites');
                break;
            case Role::CODE_DOCTORANT :
                $theses = $this->getTheseService()->getRepository()->findTheseByDoctorant($individu);
                foreach ($theses as $these) {
                    if ($these->getEtatThese() === 'E')
                        $comites[$these->getId()] = $this->getComiteSuiviService()->getComitesSuivisByThese($these);
                }
                $vm->setTemplate('comite-suivi/index/index-theses');
                break;
            case Membre::ROLE_EXAMINATEUR_CODE :
                $rawresult = $this->getComiteSuiviService()->getComitesSuivisByExaminateur($individu);
                foreach ($rawresult as $item) {
                    if ($item->getThese()->getEtatThese() === 'E') {
                        $comites[$item->getThese()->getId()] = $item;
                    }
                }
                $vm->setTemplate('comite-suivi/index/index-theses');
                break;
        }
        $vm->setVariables([
            'comites' => $comites,
            'role' => $role,
            'individu' => $individu,
            'anneeScolaire' => $this->getAnneeScolaire(),
        ]);
        return $vm;
    }
}