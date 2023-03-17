<?php

namespace RapportActivite\Assertion\Validation;

use Application\Assertion\AbstractAssertion;
use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;

class RapportActiviteValidationAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationAssertion
    {
        /** @var \Application\Service\UserContextService $userContext */
        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        /** @var RapportActiviteService $rapportActiviteService */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        /** @var \UnicaenApp\Service\MessageCollector $messageCollector */
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new RapportActiviteValidationAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setRapportActiviteService($rapportActiviteService);
        $assertion->setServiceMessageCollector($messageCollector);

        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $assertion->setValidationService($validationService);

        /** @var \RapportActivite\Service\Validation\RapportActiviteValidationService $rapportActiviteValidationService */
        $rapportActiviteValidationService = $container->get(RapportActiviteValidationService::class);
        $assertion->setRapportActiviteValidationService($rapportActiviteValidationService);

        /** @var \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(RapportActiviteOperationRule::class);
        $assertion->setRapportActiviteOperationRule($rapportActiviteOperationRule);

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