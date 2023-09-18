<?php

namespace Structure\Assertion\Structure;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\RouteMatch;
use Application\Service\UserContextService;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\StructureInterface;
use Structure\Provider\Privilege\StructurePrivileges;
use Substitution\Entity\Db\SubstitutionAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

/**
 * Class TheseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class StructureAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;

    use UserContextServiceAwareTrait;

    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    private function assertPage(array $page): bool
    {
        return true;
    }

    protected function assertEntity(ResourceInterface $structure, $privilege = null): bool
    {
        if (! parent::assertEntity($structure, $privilege)) {
            return false;
        }

        $role = $this->userContextService->getSelectedIdentityRole();
        switch (true) {
            case $privilege === StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES :
            case $privilege === StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES :
                return $role->getStructure() === $structure;
        }

        if ($structure instanceof SubstitutionAwareInterface) {
            switch (true) {
                case $privilege === StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES :
                case $privilege === StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES :
                    return $structure->updateEnabled();
            }
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $structure = $this->getRouteMatch()->getStructure();

        switch (true) {
            case $privilege === StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES :
                return parent::isAllowed(
                    $structure,
                    StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES
                );
                break;
            case $privilege === StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES :
                return parent::isAllowed(
                    $structure,
                    StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES
                );
                break;
        }

        return true;
    }

    protected function getRouteMatch(): RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}