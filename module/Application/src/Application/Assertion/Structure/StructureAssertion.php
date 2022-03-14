<?php

namespace Application\Assertion\Structure;

use Application\Assertion\BaseAssertion;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\UserContextService;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class TheseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class StructureAssertion extends BaseAssertion
{

    protected function assertEntity(ResourceInterface $structure, $privilege = null)
    {
        if (! parent::assertEntity($structure, $privilege)) {
            return false;
        }

        $role = $this->userContextService->getSelectedIdentityRole();
        switch (true) {
            case $privilege === StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES :
                return $role->getStructure() === $structure;
                break;
            case $privilege === StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES :
                return $role->getStructure() === $structure;
                break;
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null)
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $structure = $this->getRouteMatch()->getStructure();

        switch (true) {
            case $privilege === StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES :
                return $this->isAllowed(
                    $structure,
                    StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES
                );
                break;
            case $privilege === StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES :
                return $this->isAllowed(
                    $structure,
                    StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES
                );
                break;
        }

        return true;
    }

    public function isAllowed($resource, $privilege = null)
    {
        $allowed = parent::isAllowed($resource, $privilege);

        return $allowed;
    }

    /**
     * @return static
     */
    protected function initControllerAssertion()
    {
        $this->controllerAssertion->setContext([
            'structure'     => $this->getRouteMatch()->getStructure(),
        ]);

        return $this;
    }

    /**
     * @return static
     */
    protected function initPageAssertion()
    {
        $this->pageAssertion->setContext(['structure' => $this->getRouteMatch()->getStructure()]);

        return $this;
    }

    /**
     * @param ResourceInterface $entity
     * @return static
     */
    protected function initEntityAssertion(ResourceInterface $entity)
    {
        $this->entityAssertion->setContext(['structure' => $entity]);

        return $this;
    }
}