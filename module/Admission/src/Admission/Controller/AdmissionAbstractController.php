<?php

namespace Admission\Controller;

use Admission\Form\Etudiant\EtudiantForm;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Controller\Plugin\MultipageFormPlugin;
use UnicaenApp\Form\MultipageForm;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Class AdmissionAbstractController
 *
 * @method MultipageFormPlugin multipageForm(?MultipageForm $form = null)
 */
class AdmissionAbstractController extends AbstractActionController
{
    protected function processMultipageForm(EtudiantForm $form): ViewModel|Response
    {
        $response = $this->multipageForm($form)
            ->setUsePostRedirectGet()
            ->process();

        if ($response instanceof Response) {
            return $response;
        }

        $form->prepare(); // requis

        return new ViewModel($response);
    }

}