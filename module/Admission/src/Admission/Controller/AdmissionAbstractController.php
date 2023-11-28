<?php

namespace Admission\Controller;

use Admission\Form\Admission\AdmissionForm;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Controller\Plugin\MultipageFormPlugin;
use UnicaenApp\Form\MultipageForm;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Class AdmissionAbstractController
 *
 * @method MultipageFormPlugin multipageForm(?MultipageForm $form = null)
 * @method FlashMessenger flashMessenger()
 * @method boolean isAllowed($resource, $privilege = null)
 */
class AdmissionAbstractController extends AbstractActionController
{
    protected function processMultipageForm(AdmissionForm $form): ViewModel|Response
    {
        $response = $this->multipageForm($form)
            ->setUsePostRedirectGet(false)
            ->process();

        if ($response instanceof Response) {
            return $response;
        }

        return new ViewModel($response);
    }

}