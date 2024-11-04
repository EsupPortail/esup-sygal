<?php

namespace Structure\Form\Factory;

use Interop\Container\ContainerInterface;
use Structure\Form\Hydrator\UniteRechercheHydrator;
use Structure\Form\InputFilter\UniteRecherche\UniteRechercheInputFilter;
use Structure\Form\UniteRechercheForm;

class UniteRechercheFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UniteRechercheForm
    {
        $form = new UniteRechercheForm();

        /** @var UniteRechercheHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('UniteRechercheHydrator');
        $form->setHydrator($hydrator);

        /** @var \Structure\Form\InputFilter\UniteRecherche\UniteRechercheInputFilter $inputFilter */
        $inputFilter = $container->get('InputFilterManager')->get(UniteRechercheInputFilter::class);
        $form->setInputFilter($inputFilter);

        return $form;
    }
}