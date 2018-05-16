<?php

namespace Application\Service\These;

use Application\Service\Notification\NotifierServiceAwareTrait;
use Zend\View\Model\ViewModel;

class TheseObserverService
{
    use TheseServiceAwareTrait;
    use NotifierServiceAwareTrait;

    /**
     * Notification systématique à propos des thèses dont la date butoir pour le dépôt de la version corrigée est dépassée.
     */
    public function handleThesesWithDateButoirCorrectionDepassee()
    {
        $theses = $this->theseService->getRepository()->fetchThesesWithDateButoirDepotVersionCorrigeeDepassee();

        foreach ($theses as $these) {
//            $viewModel = new ViewModel([
//                'subject' => "Corrections " . lcfirst($these->getCorrectionAutoriseeToString(true)) . " non faites",
//            ]);
            $this->notifierService->triggerDateButoirCorrectionDepassee($these);
        }
    }
}