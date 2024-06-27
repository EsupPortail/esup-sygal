<?php

namespace These\Fieldset\Financement;

use Application\Entity\Db\Financement;
use Application\Service\Discipline\DisciplineService;
use Application\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;
use These\Entity\Db\These;

class FinancementFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FinancementFieldset
    {
        $fieldset = new FinancementFieldset('Financement');
        /** @var FinancementHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(FinancementHydrator::class);
        $fieldset->setHydrator($hydrator);

        $fieldset->setObject(new Financement());

        return $fieldset;
    }
}