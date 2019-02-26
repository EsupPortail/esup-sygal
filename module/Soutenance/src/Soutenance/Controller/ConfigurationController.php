<?php

namespace Soutenance\Controller;

use Soutenance\Entity\Parametre;
use Soutenance\Form\Configuration\ConfigurationForm;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ConfigurationController extends  AbstractActionController {
    use ParametreServiceAwareTrait;

    public function indexAction()
    {
        /** @var ConfigurationForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(ConfigurationForm::class);

        //todo faire une fonction dans le service ...
        $params = [
            Parametre::CODE_AVIS_DEADLINE,
            Parametre::CODE_JURY_SIZE_MIN,
            Parametre::CODE_JURY_SIZE_MAX,
            Parametre::CODE_JURY_RAPPORTEUR_SIZE_MIN,
            Parametre::CODE_JURY_RANGA_RATIO_MIN,
            Parametre::CODE_JURY_EXTERIEUR_RATIO_MIN,
            Parametre::CODE_JURY_PARITE_RATIO_MIN,
        ];

        foreach ($params as $param) {
            $form->get($param)->setValue($this->getParametreService()->getParametreByCode($param)->getValeur());
        }

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            foreach ($data as $key => $valeur) {
                if (array_search($key, $params) !== false) {
                    $parametre = $this->getParametreService()->getParametreByCode($key)->setValeur($valeur);
                    $parametre = $this->getParametreService()->update($parametre);
                }
            }
            $this->redirect()->toRoute('configuration');
        }



        return new ViewModel([
            'form' => $form,
        ]);
    }
}