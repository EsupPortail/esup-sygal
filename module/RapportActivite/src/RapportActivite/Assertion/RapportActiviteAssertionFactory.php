<?php

namespace RapportActivite\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Psr\Container\ContainerInterface;
use RapportActivite\Assertion\RapportActiviteAssertion;
use RapportActivite\Rule\Televersement\RapportActiviteTeleversementRule;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAssertion
    {
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new RapportActiviteAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setRapportActiviteService($rapportActiviteService);
        $assertion->setServiceMessageCollector($messageCollector);

        /** @var \Application\Service\TheseAnneeUniv\TheseAnneeUnivService $theseAnneeUnivService */
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);

        /** @var \RapportActivite\Rule\Televersement\RapportActiviteTeleversementRule $rapportActiviteTeleversementRule */
        $rapportActiviteTeleversementRule = $container->get(RapportActiviteTeleversementRule::class);
        $rapportActiviteTeleversementRule->setAnneesUnivs([
            $theseAnneeUnivService->anneeUnivCourante(),
            $theseAnneeUnivService->anneeUnivPrecedente(),
        ]);
        $assertion->setRapportActiviteTeleversementRule($rapportActiviteTeleversementRule);

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