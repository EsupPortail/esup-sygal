<?php

namespace Application;

use Application\Service\MailConfirmationService;
use Application\Service\UserContextService;
use Doctorant\Entity\Db\Doctorant;

/**
 * Détournement de la requête en fonction de la connaissance de l'adresse email de contact du doctorant.
 */
class SaisieEmailContactRouteDeflector extends RouteDeflector
{
    protected ?Doctorant $doctorant = null;

    protected function isActivated(): bool
    {
        $doctorant = $this->getDoctorant();
        if (!$doctorant) {
            return false;
        }

        /** @var MailConfirmationService $mailConfirmationService */
        $mailConfirmationService = $this->event->getApplication()->getServiceManager()->get('MailConfirmationService');
        $mailConfirmation = $mailConfirmationService->fetchMailConfirmationForIndividu($doctorant->getIndividu());

        // pas de détournement de la requête si l'email a été confirmé
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