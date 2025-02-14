<?php

namespace Soutenance\Rule;

use UnicaenParametre\Service\Parametre\ParametreService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PropositionJuryRuleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): PropositionJuryRule
    {
        $rule = new PropositionJuryRule();

        /** @var \UnicaenParametre\Service\Parametre\ParametreService $parametreService */
        $parametreService = $container->get(ParametreService::class);
        $rule->setParametreService($parametreService);

        return $rule;
    }
}