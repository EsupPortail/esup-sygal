<?php

namespace RapportActivite\Form;

use Application\Service\AnneeUniv\AnneeUnivService;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\Strategy\BooleanStrategy;
use Psr\Container\ContainerInterface;

class RapportActiviteAnnuelFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAnnuelForm
    {
        $form = new RapportActiviteAnnuelForm('rapport-activite');

        $hydrator = new ClassMethodsHydrator(false);
        $hydrator->addStrategy('estFinContrat', new BooleanStrategy('1', '0'));
        $form->setHydrator($hydrator);

        /** @var \Application\Service\AnneeUniv\AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $form->setAnneeUnivService($anneeUnivService);

        return $form;
    }
}