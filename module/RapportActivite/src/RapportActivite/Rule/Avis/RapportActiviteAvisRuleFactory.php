<?php

namespace RapportActivite\Rule\Avis;

use Psr\Container\ContainerInterface;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use UnicaenAvis\Service\AvisService;

/**
 * @deprecated
 */
class RapportActiviteAvisRuleFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAvisRule
    {
        $rule = new RapportActiviteAvisRule();

        /** @var \RapportActivite\Service\Avis\RapportActiviteAvisService $rapportActiviteAvisService */
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        $rule->setRapportActiviteAvisService($rapportActiviteAvisService);

        /** @var \UnicaenAvis\Service\AvisService $avisService */
        $avisService = $container->get(AvisService::class);
        $rule->setAvisService($avisService);

        return $rule;
    }
}