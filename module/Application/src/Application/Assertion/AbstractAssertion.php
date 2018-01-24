<?php

namespace Application\Assertion;

use UnicaenAuth\Provider\Privilege\Privileges;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class AbstractAssertion
 *
 * @package Application\Assertion
 */
abstract class AbstractAssertion extends \UnicaenAuth\Assertion\AbstractAssertion
{
    protected function assertEntity(ResourceInterface $entity, $privilege = null)
    {
        // Patch pour corriger le fonctionnement aberrant suivant :
        // On passe dans l'assertion même si le rôle ne possède par le privilège !
        if (! $this->getAcl()->isAllowed($this->getRole(), Privileges::getResourceId($privilege))) {
            return false;
        }

        return true;
    }
}