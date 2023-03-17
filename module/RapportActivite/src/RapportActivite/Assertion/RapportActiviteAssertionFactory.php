<?php

namespace RapportActivite\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Service\AnneeUniv\AnneeUnivService;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Creation\RapportActiviteCreationRule;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Service\RapportActiviteService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class RapportActiviteAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAssertion
    {
        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new RapportActiviteAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setRapportActiviteService($rapportActiviteService);
        $assertion->setServiceMessageCollector($messageCollector);

        /** @var \These\Service\TheseAnneeUniv\TheseAnneeUnivService $theseAnneeUnivService */
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);

        /** @var \RapportActivite\Rule\Creation\RapportActiviteCreationRule $rapportActiviteTeleversementRule */
        $rapportActiviteTeleversementRule = $container->get(RapportActiviteCreationRule::class);
        /** @var \Application\Service\AnneeUniv\AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $rapportActiviteTeleversementRule->setAnneesUnivs([
            $anneeUnivService->courante(),
            $anneeUnivService->precedente(),
        ]);
        $assertion->setRapportActiviteCreationRule($rapportActiviteTeleversementRule);

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