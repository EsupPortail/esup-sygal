<?php

namespace Admission\Assertion\Validation;

use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\TypeValidation\TypeValidationService;
use Admission\Service\Validation\AdmissionValidationService;
use Application\Assertion\AbstractAssertion;
use Application\Service\UserContextService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenApp\Service\MessageCollector;

class AdmissionValidationAssertionFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionValidationAssertion
    {
        /** @var UserContextService $userContext */
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        /** @var MessageCollector $messageCollector */
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new AdmissionValidationAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setAdmissionService($admissionService);
        $assertion->setServiceMessageCollector($messageCollector);

        /** @var TypeValidationService $typeValidationService */
        $typeValidationService = $container->get(TypeValidationService::class);
        $assertion->setTypeValidationService($typeValidationService);

        /** @var AdmissionValidationService $admissionValidationService */
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        $assertion->setAdmissionValidationService($admissionValidationService);

        /** @var AdmissionOperationRule $admissionOperationRule */
        $admissionOperationRule = $container->get(AdmissionOperationRule::class);
        $assertion->setAdmissionOperationRule($admissionOperationRule);

        $this->injectCommons($assertion, $container);

        return $assertion;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function injectCommons(AbstractAssertion $assertion, ContainerInterface $container)
    {
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        $mvcEvent = $container->get('Application')->getMvcEvent();

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
    }
}