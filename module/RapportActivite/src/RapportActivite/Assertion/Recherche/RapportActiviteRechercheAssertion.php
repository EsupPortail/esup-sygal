<?php

namespace RapportActivite\Assertion\Recherche;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Service\UserContextServiceAwareInterface;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class RapportActiviteRechercheAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;

    private ?These $these = null;

    /**
     * @param array $page
     * @return bool
     */
    public function __invoke(array $page): bool
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
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        if (!$this->initForControllerAction($action)) {
            return false;
        }

        try {

            switch ($action) {
                case 'index':
                case 'filters':
                case 'telecharger-zip':
                    $role = $this->userContextService->getSelectedIdentityRole();
                    if (!$role) {
                        return false;
                    }
                    //todo : ajouter un nouveau privilège afin que le doctorant n'ait pas accès à l'index des rapports d'activité (conflit avec les gest/resp ed/ur)
                    if ($role->isDoctorant()) {
                        return false;
                    }
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

    private function initForControllerAction(string $action): bool
    {
        switch ($action) {
            case 'index':
            case 'filters':
            case 'telecharger-zip':
                break;

            default:
                throw new InvalidArgumentException(__METHOD__  . " : Action inattendue : " . $action);
        }

        return true;
    }

    /**
     * @param \These\Entity\Db\These $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        try {

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

}