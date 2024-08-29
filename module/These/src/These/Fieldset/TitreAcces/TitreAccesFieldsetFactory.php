<?php

namespace These\Fieldset\TitreAcces;

use Application\Service\TitreAcces\TitreAccesService;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class TitreAccesFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TitreAccesFieldset
    {
        $fieldset = new TitreAccesFieldset();
        $fieldset->setName("titreAcces");

        /** @var TitreAccesHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);
        $fieldset->setHydrator($hydrator);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);
        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $fieldset->setEntityManager($entityManager);

        /** @var TitreAccesService $titreAccesService */
        $titreAccesService = $container->get(TitreAccesService::class);
        $fieldset->setObject($titreAccesService->newTitreAcces());

        return $fieldset;
    }
}