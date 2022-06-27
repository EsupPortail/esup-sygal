<?php

namespace Individu\Form;

use Application\Service\Pays\PaysService;
use Individu\Hydrator\IndividuHydrator;
use Psr\Container\ContainerInterface;

class IndividuFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuForm
    {
        /** @var IndividuHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(IndividuHydrator::class);

        $form = new IndividuForm();
        $form->setPays($this->fetchPaysNationalites($container));
        $form->setHydrator($hydrator);

        return $form;
    }

    /**
     * @param \Psr\Container\ContainerInterface $container
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function fetchPaysNationalites(ContainerInterface $container): array
    {
        /** @var PaysService $paysService */
        $paysService = $container->get(PaysService::class);
        $qb = $paysService->getRepository()->createQueryBuilder('p')
            ->where('p.libelleNationalite is not null')
            ->orderBy('p.libelleNationalite');

        return $qb->getQuery()->getArrayResult();
    }
}