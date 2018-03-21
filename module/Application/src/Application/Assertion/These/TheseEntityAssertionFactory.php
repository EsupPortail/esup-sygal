<?php

namespace Application\Assertion\These;

use Application\Service\Fichier\FichierServiceLocateTrait;
use Application\Service\UserContextServiceLocateTrait;
use Application\Service\Validation\ValidationServiceLocateTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class TheseEntityAssertionFactory
{
    use UserContextServiceLocateTrait;
    use FichierServiceLocateTrait;
    use ValidationServiceLocateTrait;

    public function __invoke(ServiceLocatorInterface $sl)
    {
        $assertion = new TheseEntityAssertion();
        $assertion->setUserContextService($this->locateUserContextService($sl));
        $assertion->setFichierService($this->locateFichierService($sl));
        $assertion->setValidationService($this->locateValidationService($sl));

        return $assertion;
    }
}