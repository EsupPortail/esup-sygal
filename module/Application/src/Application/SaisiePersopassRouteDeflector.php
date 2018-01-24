<?php

namespace Application;

use Application\Entity\Db\Doctorant;
use Application\Service\UserContextService;
use Zend\Http\Response as HttpResponse;

/**
 * Class SaisiePersopassRouteDeflector
 *
 * @package Application
 */
class SaisiePersopassRouteDeflector extends RouteDeflector
{
    protected $doctorant;

    protected function isActivated()
    {
        return $this->getDoctorant() && ! $this->getDoctorant()->getPersopass();
    }

    protected function prepareRedirectArgument()
    {
        // injection du paramètre doctorant dans la route
        $this->redirect['params']['doctorant'] = $this->getDoctorant()->getId();

        return $this;
    }

    /**
     * @return Doctorant
     */
    protected function getDoctorant()
    {
        if ($this->doctorant !== null) {
            return $this->doctorant;
        }

        /** @var UserContextService $userContext */
        $userContext = $this->event->getApplication()->getServiceManager()->get('UnicaenAuth\Service\UserContext');

        $this->doctorant = $userContext->getIdentityDoctorant();

        return $this->doctorant;
    }
}