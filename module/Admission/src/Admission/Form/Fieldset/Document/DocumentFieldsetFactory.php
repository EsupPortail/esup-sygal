<?php

namespace Admission\Form\Fieldset\Document;

use Admission\Entity\Db\Document;
use Admission\Hydrator\Document\DocumentHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class DocumentFieldsetFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DocumentFieldset
    {
        /** @var DocumentHydrator $documentHydrator */
        $documentHydrator = $container->get('HydratorManager')->get(DocumentHydrator::class);

        $fieldset = new DocumentFieldset();
        $fieldset->setHydrator($documentHydrator);
        $fieldset->setObject(new Document());

        return $fieldset;
    }
}