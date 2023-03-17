<?php

namespace Admission\Assertion;

use Admission\Service\Admission\AdmissionService;
use Application\Assertion\AbstractAssertion;
use Individu\Service\IndividuService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenAuthentification\Service\UserContext;

class AdmissionAssertionFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionAssertion
    {
        $userContext = $container->get(UserContext::class);
        $admission = $container->get(AdmissionService::class);
        $individu = $container->get(IndividuService::class);
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new AdmissionAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setAdmissionService($admission);
        $assertion->setIndividuService($individu);
        $assertion->setServiceMessageCollector($messageCollector);

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