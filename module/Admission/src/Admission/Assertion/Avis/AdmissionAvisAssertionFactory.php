<?php

namespace Admission\Assertion\Avis;

use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Avis\AdmissionAvisService;
use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;
use UnicaenAuthentification\Service\UserContext;
use UnicaenAvis\Service\AvisService;

class AdmissionAvisAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionAvisAssertion
    {
        $userContext = $container->get(UserContext::class);
        $admissionService = $container->get(AdmissionService::class);
        $admissionAvisService = $container->get(AdmissionAvisService::class);
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new AdmissionAvisAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setAdmissionService($admissionService);
        $assertion->setAdmissionAvisService($admissionAvisService);
        $assertion->setServiceMessageCollector($messageCollector);

        /** @var AdmissionOperationRule $admissionOperationRule */
        $admissionOperationRule = $container->get(AdmissionOperationRule::class);
        $assertion->setAdmissionOperationRule($admissionOperationRule);

        /** @var AvisService $admissionAvisRule */
        $avisService = $container->get(AvisService::class);
        $assertion->setAvisService($avisService);

        $this->injectCommons($assertion, $container);

        return $assertion;
    }

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    protected function injectCommons(AbstractAssertion $assertion, ContainerInterface $container)
    {
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        $mvcEvent = $container->get('Application')->getMvcEvent();

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
    }
}