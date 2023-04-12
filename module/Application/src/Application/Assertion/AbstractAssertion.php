<?php

namespace Application\Assertion;

use Application\RouteMatch;
use Application\Service\UserContextService;
use Application\Service\UserContextServiceAwareTrait;
use Depot\Acl\WfEtapeResource;
use Depot\Service\Workflow\WorkflowServiceAwareInterface;
use Depot\Service\Workflow\WorkflowServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use UnicaenApp\Service\MessageCollectorAwareTrait;
use UnicaenAuth\Provider\Privilege\Privileges;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class AbstractAssertion
 *
 * @method UserContextService getServiceUserContext()
 */
abstract class AbstractAssertion extends \UnicaenAuth\Assertion\AbstractAssertion
    implements WorkflowServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use WorkflowServiceAwareTrait;
    use MessageCollectorAwareTrait;

    protected ?string $controller = null;
    protected ?string $action = null;

    protected function assertEntity(ResourceInterface $entity, $privilege = null)
    {
        // Patch pour corriger le fonctionnement aberrant suivant :
        // On passe dans l'assertion même si le rôle ne possède par le privilège !
        if (! $this->getAcl()->isAllowed($this->getRole(), Privileges::getResourceId($privilege))) {
            return false;
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null)
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $this->controller = $controller;
        $this->action = $action;

        return true;
    }

    /**
     * @param string $expectedController
     * @param string $expectedAction
     * @return bool
     */
    protected function actionIs(string $expectedController, string $expectedAction): bool
    {
        return $this->controller === $expectedController && $this->action === $expectedAction;
    }

    /**
     * @return bool
     */
    protected function selectedRoleIsDoctorant(): bool
    {
        return (bool) $this->userContextService->getSelectedRoleDoctorant();
    }

    protected ?Doctorant $identityDoctorant = null;

    protected function getIdentityDoctorant(): ?Doctorant
    {
        if (null === $this->identityDoctorant) {
            $this->identityDoctorant = $this->userContextService->getIdentityDoctorant();
        }

        return $this->identityDoctorant;
    }

    public function isAllowed($resource, $privilege = null): bool
    {
        $allowed = parent::isAllowed($resource, $privilege);

        if (! $allowed) {
            switch (true) {
                case $resource instanceof WfEtapeResource:
                    $etape = $this->workflowService->getEtapeRepository()->findOneBy(['code' => $resource->getEtape()]);
                    $this->getServiceMessageCollector()->addMessage(
                        sprintf("L'étape &laquo; %s &raquo; n'est pas encore accessible.", $etape->getLibelleAutres()),
                        $etape->getCode());
                    break;
                default:
                    break;
            }
        }

        return $allowed;
    }

    protected function getRouteMatch()
    {
        /** @var \Application\RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();

        return $rm;
    }
}