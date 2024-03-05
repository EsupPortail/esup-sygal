<?php

namespace Admission\Assertion\ConventionFormationDoctorale;

use Admission\Service\Admission\AdmissionService;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleService;
use Application\Assertion\AbstractAssertion;
use Application\Service\UserContextService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenApp\Service\MessageCollector;

class ConventionFormationDoctoraleAssertionFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ConventionFormationDoctoraleAssertion
    {
        /** @var UserContextService $userContext */
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        /** @var MessageCollector $messageCollector */
        $messageCollector = $container->get('MessageCollector');

        $conventionFormationDoctoraleService = $container->get(ConventionFormationDoctoraleService::class);

        /** @var  $assertion */
        $assertion = new ConventionFormationDoctoraleAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setAdmissionService($admissionService);
        $assertion->setServiceMessageCollector($messageCollector);
        $assertion->setConventionFormationDoctoraleService($conventionFormationDoctoraleService);

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