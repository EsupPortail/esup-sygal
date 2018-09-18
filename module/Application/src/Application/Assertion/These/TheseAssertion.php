<?php

namespace Application\Assertion\These;

use Application\Acl\WfEtapeResource;
use Application\Assertion\BaseAssertion;
use Application\Entity\Db\These;
use Application\Entity\Db\WfEtape;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\UserContextService;
use Application\Service\Workflow\WorkflowServiceAwareInterface;
use Application\Service\Workflow\WorkflowServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareTrait;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class TheseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class TheseAssertion extends BaseAssertion implements WorkflowServiceAwareInterface
{
    use WorkflowServiceAwareTrait;
    use MessageCollectorAwareTrait;

    protected function assertEntity(ResourceInterface $these, $privilege = null)
    {
        if (! parent::assertEntity($these, $privilege)) {
            return false;
        }

        /** @var These $these */

        switch (true) {
            case $privilege === ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE:
                return ! $this->isAllowed(new WfEtapeResource(WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE, $these));
                break;
            case $privilege === ValidationPrivileges::THESE_VALIDATION_RDV_BU:
                return $this->isAllowed(new WfEtapeResource(WfEtape::CODE_RDV_BU_VALIDATION_BU, $these));
                break;
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null)
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $these = $this->getRouteMatch()->getThese();

        switch (true) {
            case $privilege === ValidationPrivileges::THESE_VALIDATION_RDV_BU:
                return $this->isAllowed(new WfEtapeResource(WfEtape::CODE_RDV_BU_VALIDATION_BU, $these));
                break;
        }

        return true;
    }

    public function isAllowed($resource, $privilege = null)
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

    /**
     * @return static
     */
    protected function initControllerAssertion()
    {
        $this->controllerAssertion->setContext([
            'these'     => $this->getRouteMatch()->getThese(),
            'doctorant' => $this->getRouteMatch()->getDoctorant(),
        ]);

        return $this;
    }

    /**
     * @return static
     */
    protected function initPageAssertion()
    {
        $this->pageAssertion->setContext(['these' => $this->getRouteMatch()->getThese()]);

        return $this;
    }

    /**
     * @param ResourceInterface $entity
     * @return static
     */
    protected function initEntityAssertion(ResourceInterface $entity)
    {
        $this->entityAssertion->setContext(['these' => $entity]);

        return $this;
    }
}