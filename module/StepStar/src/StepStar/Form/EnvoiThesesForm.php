<?php

namespace StepStar\Form;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;

class EnvoiForm extends AbstractEnvoiForm
{
    public function init(): void
    {
        parent::init();

        $this->add((new Text('these'))
            ->setLabel("Numéros de thèses (séparés par une virgule) :")
        );

        $this->add((new Checkbox('force'))
            ->setLabel("Envoyer même si le TEF n'a pas changé depuis le dernier envoi")
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return array_merge(parent::getInputFilterSpecification(), [
            'these' => [
                'name' => 'these',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],
            'force' => [
                'name' => 'force',
                'required' => false,
            ],
        ]);
    }
}