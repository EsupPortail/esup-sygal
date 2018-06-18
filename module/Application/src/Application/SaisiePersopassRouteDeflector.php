<?php

namespace Application;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\MailConfirmation;
use Application\Service\MailConfirmationService;
use Application\Service\UserContextService;
use Zend\Http\Response as HttpResponse;

/**
 * Cette fonction est appelée pour tester si un doctorant à bien fourni les infos necessaires de mise en relation
 * entre son compte et lui
 * Pré SyGAL :: avoir rempli son persopass
 * SyGAL :: avoir fournis un adresse de contact et l'avoir vérifier
 *
 * Class SaisiePersopassRouteDeflector
 *
 * @package Application
 */
class SaisiePersopassRouteDeflector extends RouteDeflector
{
    protected $doctorant;

    protected function isActivated()
    {
        /** @var Doctorant $doctorant */
        $doctorant = $this->getDoctorant();
        if (!$doctorant) return false;

        /** @var MailConfirmationService $mailConfirmationService */
        $mailConfirmationService = $this->event->getApplication()->getServiceManager()->get('MailConfirmationService');
        $confirmedEmail = $mailConfirmationService->getDemandeConfirmeeByIndividu($doctorant->getIndividu());
        if ($confirmedEmail) return false;

        return true;

        //Code de Sodoct commenté pour mémoire
//        $hasPersoPass = $this->getDoctorant()->getPersopass();
//        if (!$hasPersoPass) return false;
//        return $this->getDoctorant() && ! $this->getDoctorant()->getPersopass();
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