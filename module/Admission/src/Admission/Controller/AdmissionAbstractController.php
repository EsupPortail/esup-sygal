<?php

namespace Admission\Controller;

use Application\Controller\Plugin\LogMessenger;
use Application\Controller\Plugin\Notifier;
use UnicaenApp\Controller\Plugin\MultipageFormPlugin;
use UnicaenApp\Form\MultipageForm;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Class AdmissionAbstractController
 *
 * @method MultipageFormPlugin multipageForm(?MultipageForm $form = null)
 */
class AdmissionAbstractController extends AbstractActionController
{
}