<?php

namespace Doctorant\Form;

use Doctorant\Hydrator\ConsentementHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MailConsentementFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MailConsentementForm
    {
        $form = new MailConsentementForm('consentement');

        $form->setHydrator(new ConsentementHydrator());

        return $form;
    }
}