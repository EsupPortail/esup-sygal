<?php

namespace Doctorant\Form\Doctorant;

use Doctorant\Entity\Db\Doctorant;
use Doctorant\Hydrator\Doctorant\DoctorantHydrator;
use Psr\Container\ContainerInterface;

class DoctorantFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantForm
    {
        /** @var DoctorantHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DoctorantHydrator::class);

        $form = new DoctorantForm();
        $form->setObject(new Doctorant());
        $form->setHydrator($hydrator);

        return $form;
    }
}