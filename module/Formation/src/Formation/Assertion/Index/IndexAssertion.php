<?php

namespace Formation\Assertion\Index;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Service\UserContextServiceAwareInterface;
use Formation\Entity\Db\FormationExterne;
use JetBrains\PhpStorm\Pure;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class IndexAssertion extends AbstractAssertion implements  AssertionInterface,  UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;


    /**
     * @param array $page
     * @return bool
     */
    #[Pure] public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    /**
     * @param array $page
     * @return bool
     */
    private function assertPage(array $page): bool
    {
        return true;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (!parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $these = $this->getRequestedThese();
        if(!$these) return false;

        try {
            switch($action) {
                case 'index-maison-doctorat' :
                    $this->assertAppartenanceThese($these);
                    break;
            }
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    /** @var FormationExterne $entity */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }

        return true;
    }
    private function getRequestedThese(): ?These
    {
        if ($routeMatch = $this->getRouteMatch()) {
            return $routeMatch->getThese();
        } else {
            return null;
        }
    }
    private function assertAppartenanceThese(These $these)
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        if ($role->isDoctorant()) {
            $doctorant = $this->userContextService->getIdentityDoctorant();
            $this->assertTrue(
                $these->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        }
        elseif ($roleBDD = $this->userContextService->getSelectedRoleBDD()) {
            $this->assertTrue(
                $these->getEtablissement()->getStructure()->getId() === $roleBDD->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'établissement " . $roleBDD->getStructure()->getCode()
            );
        }
    }
}