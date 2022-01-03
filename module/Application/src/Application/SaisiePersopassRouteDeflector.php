<?php

namespace Application;

use Application\Service\MailConfirmationService;
use Application\Service\UserContextService;
use Doctorant\Entity\Db\Doctorant;

/**
 * Class SaisiePersopassRouteDeflector
 *
 * @package Application
 */
class SaisiePersopassRouteDeflector extends RouteDeflector
{
    protected $doctorant;

    protected function isActivated(): bool
    {
        $doctorant = $this->getDoctorant();
        if (!$doctorant) {
            return false;
        }

        /** @var MailConfirmationService $mailConfirmationService */
        $mailConfirmationService = $this->event->getApplication()->getServiceManager()->get('MailConfirmationService');
        $mailConfirmation = $mailConfirmationService->fetchMailConfirmationsForIndividu($doctorant->getIndividu());
        if ($mailConfirmation && $mailConfirmation->estConfirme()) {
            return false;
        }

        return true;
    }

    protected function prepareRedirectArgument(): self
    {
        // injection du paramètre doctorant dans la route
        $this->redirect['params']['doctorant'] = $this->getDoctorant()->getId();

        return $this;
    }

    /**
     * @return Doctorant|null
     */
    protected function getDoctorant(): ?Doctorant
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