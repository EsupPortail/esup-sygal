<?php

namespace RapportActivite\Assertion\Validation;

use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Validation\RapportActiviteValidationRule;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteValidationAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationAssertion
    {
        /** @var \Application\Service\UserContextService $userContext */
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        /** @var RapportActiviteService $rapportActiviteService */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        /** @var RapportActiviteAvisService $rapportActiviteAvisService */
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        /** @var RapportActiviteValidationRule $rapportActiviteValidationRule */
        $rapportActiviteValidationRule = $container->get(RapportActiviteValidationRule::class);
        /** @var \UnicaenApp\Service\MessageCollector $messageCollector */
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new RapportActiviteValidationAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setRapportActiviteService($rapportActiviteService);
        $assertion->setRapportActiviteAvisService($rapportActiviteAvisService);
        $assertion->setRapportActiviteValidationRule($rapportActiviteValidationRule);
        $assertion->setServiceMessageCollector($messageCollector);

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