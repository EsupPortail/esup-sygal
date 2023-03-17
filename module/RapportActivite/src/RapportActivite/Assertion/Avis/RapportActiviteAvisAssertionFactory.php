<?php

namespace RapportActivite\Assertion\Avis;

use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\RapportActiviteService;
use UnicaenAvis\Service\AvisService;

class RapportActiviteAvisAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAvisAssertion
    {
        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new RapportActiviteAvisAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setRapportActiviteService($rapportActiviteService);
        $assertion->setRapportActiviteAvisService($rapportActiviteAvisService);
        $assertion->setServiceMessageCollector($messageCollector);

        /** @var \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(RapportActiviteOperationRule::class);
        $assertion->setRapportActiviteOperationRule($rapportActiviteOperationRule);

        /** @var \UnicaenAvis\Service\AvisService $rapportActiviteAvisRule */
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