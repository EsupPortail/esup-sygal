<?php

namespace UnicaenAvis\Fieldset;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Entity\Db\AvisValeur;
use UnicaenAvis\Hydrator\AvisValeurHydrator;

class AvisValeurFieldsetFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AvisValeurFieldset
    {
        /** @var \UnicaenAvis\Hydrator\AvisValeurHydrator $avisValeurHydrator */
        $avisValeurHydrator = $container->get('HydratorManager')->get(AvisValeurHydrator::class);

        $fieldset = new AvisValeurFieldset();
        $fieldset->setHydrator($avisValeurHydrator);
        $fieldset->setObject(new AvisValeur());

        return $fieldset;
    }
}