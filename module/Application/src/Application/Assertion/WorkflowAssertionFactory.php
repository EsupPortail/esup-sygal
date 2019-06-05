<?php

namespace Application\Assertion;

use Application\Service\UserContextService;
use Application\Service\Workflow\WorkflowService;
use UnicaenAuth\Service\UserContext;
use Zend\ServiceManager\ServiceLocatorInterface;

class WorkflowAssertionFactory
{
    public function __invoke(ServiceLocatorInterface $container)
    {
        /**
         * @var WorkflowService $wfService
         * @var UserContext $ucService
         */
        $wfService = $container->get('WorkflowService');
        $ucService = $container->get('UnicaenAuth\Service\UserContext');

        $assertion = new WorkflowAssertion();
        $assertion->setWorkflowService($wfService);
        $assertion->setServiceUserContext($ucService);
        $assertion->setServiceLocator($container);

        return $assertion;
    }
}