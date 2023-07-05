<?php

namespace RapportActivite\Form;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use RapportActivite\Hydrator\RapportActiviteHydrator;

class RapportActiviteAnnuelFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAnnuelForm
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new RapportActiviteHydrator($em);

        $form = new RapportActiviteAnnuelForm('rapport-activite');
        $form->setHydrator($hydrator);

        /** @var \Application\Service\AnneeUniv\AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $form->setAnneeUnivService($anneeUnivService);

        return $form;
    }
}