<?php

namespace StepStar\Form\Envoi;

use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

abstract class AbstractEnvoiForm extends Form implements InputFilterProviderInterface
{
    public function init(): void
    {
        parent::init();

        $this->add((new Submit('submit'))
            ->setValue("Envoyer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'tag' => [
                'name' => 'tag',
                'required' => false,
            ],
        ];
    }
}