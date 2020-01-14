<?php

namespace Soutenance\Controller\Configuration;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Soutenance\Entity\Parametre;
use Soutenance\Form\Configuration\ConfigurationForm;
use Soutenance\Form\Configuration\ConfigurationFormAwareTrait;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ConfigurationController extends  AbstractActionController {
    use ParametreServiceAwareTrait;
    use ConfigurationFormAwareTrait;

    public function indexAction()
    {
        /** @var ConfigurationForm $form */
        $form = $this->getConfigurationForm();

        //todo faire une fonction dans le service ...
        $params = [
            Parametre::CODE_AVIS_DEADLINE,
            Parametre::CODE_JURY_SIZE_MIN,
            Parametre::CODE_JURY_SIZE_MAX,
            Parametre::CODE_JURY_RAPPORTEUR_SIZE_MIN,
            Parametre::CODE_JURY_RANGA_RATIO_MIN,
            Parametre::CODE_JURY_EXTERIEUR_RATIO_MIN,
            Parametre::CODE_JURY_PARITE_RATIO_MIN,

            Parametre::CODE_FORMULAIRE_DELOCALISATION,
            Parametre::CODE_FORMULAIRE_DELEGUATION,
            Parametre::CODE_FORMULAIRE_THESE_ANGLAIS,
            Parametre::CODE_FORMULAIRE_LABEL_EUROPEEN,
            Parametre::CODE_FORMULAIRE_CONFIDENTIALITE,
        ];

        foreach ($params as $param) {
            $element = $form->get($param);
            $value = $this->getParametreService()->getParametreByCode($param);

            if($value !== null) $form->get($param)->setValue($this->getParametreService()->getParametreByCode($param)->getValeur());
        }

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            foreach ($data as $key => $valeur) {
                if (array_search($key, $params) !== false) {
                    $parametre = $this->getParametreService()->getParametreByCode($key)->setValeur($valeur);
                    $this->getParametreService()->update($parametre);
                }
            }
            $this->redirect()->toRoute('configuration');
        }



        return new ViewModel([
            'form' => $form,
        ]);
    }
}