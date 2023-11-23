<?php

namespace Substitution\Controller;

use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Substitution\Service\SubstitutionServiceAwareTrait;

/**
 * @method FlashMessenger flashMessenger()
 */
class SubstitutionController extends SubstitutionAbstractController
{
    use SubstitutionServiceAwareTrait;

    public function accueilAction(): ViewModel
    {
        return (new ViewModel([

        ]))->setTemplate('substitution/substitution/accueil');
    }
}