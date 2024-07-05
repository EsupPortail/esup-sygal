<?php

namespace These\Fieldset\Financement;

use Application\Service\Financement\FinancementService;
use Application\Service\Source\SourceService;
use Doctorant\Service\DoctorantService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class FinancementHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FinancementHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        return new FinancementHydrator($entityManager);
    }
}