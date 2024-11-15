<?php

namespace StepStar\Form\Envoi;

use Application\Utils\FormUtils;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

abstract class AbstractEnvoiForm extends Form implements InputFilterProviderInterface
{
    public function init(): void
    {
        parent::init();

        FormUtils::addSaveButton($this, "Envoyer");
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