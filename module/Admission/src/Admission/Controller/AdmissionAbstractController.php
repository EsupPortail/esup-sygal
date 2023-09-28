<?php

namespace Admission\Controller;

use Admission\Form\Etudiant\EtudiantForm;
use Laminas\Http\Response;
use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Controller\Plugin\MultipageFormPlugin;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\MultipageForm;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Class AdmissionAbstractController
 *
 * @method MultipageFormPlugin multipageForm(?MultipageForm $form = null)
 */
class AdmissionAbstractController extends AbstractActionController
{
    protected function processMultipageForm(EtudiantForm $form)
    {
        $response = $this->multipageForm($form)
            ->setUsePostRedirectGet()
            ->process();
//        var_dump($this->multipageForm($form)->getFormSessionData());
        if ($response instanceof Response) {
            return $response;
        }

        $form->prepare(); // requis

        return new ViewModel($response);
    }

}