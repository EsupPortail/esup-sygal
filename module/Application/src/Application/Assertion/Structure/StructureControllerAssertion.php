<?php

namespace Application\Assertion\Structure;

use Application\Assertion\ControllerAssertion;
use Application\Entity\Db\Structure;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\UserContextServiceAwareTrait;

class StructureControllerAssertion extends ControllerAssertion
{
    use UserContextServiceAwareTrait;

    const THESE_CONTROLLER = 'Application\Controller\These';
    const DOCTORANT_CONTROLLER = 'Application\Controller\Doctorant';

    /**
     * @var Structure
     */
    private $structure;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        parent::setContext($context);

        if (array_key_exists('structure', $context)) {
            $this->structure = $context['structure'];
        }
    }

    /**
     * @param string $privilege
     * @return boolean
     */
    public function assert($privilege = null)
    {
        switch($privilege) {
            case StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES :
                return true;
            case StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES :
                return true;
        }
        return true;
    }


}