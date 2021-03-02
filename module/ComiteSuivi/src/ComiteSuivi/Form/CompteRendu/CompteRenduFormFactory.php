<?php

namespace ComiteSuivi\Form\CompteRendu;

use ComiteSuivi\Service\Membre\MembreService;
use ComiteSuivi\Service\Membre\MembreServiceAwareTrait;
use Interop\Container\ContainerInterface;
use Zend\Form\FormElementManager;

class CompteRenduFormFactory {
    use MembreServiceAwareTrait;

    /**
     * @param ContainerInterface $container
     * @return CompteRenduForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var MembreService $membreService
         */
        $membreService = $container->get(MembreService::class);

        /** @var CompteRenduHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(CompteRenduHydrator::class);

        $form = new CompteRenduForm();
        $form->setMembreService($membreService);
        $form->setHydrator($hydrator);
        return $form;
    }
}