<?php

namespace StepStar\Form;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class EnvoiForm extends Form implements InputFilterProviderInterface
{
    public function init()
    {
        parent::init();

        $this->add((new Text('these'))
            ->setLabel("Numéros de thèses (séparés par une virgule) :")
        );

        $this->add((new Checkbox('force'))
            ->setLabel("Envoyer même si le TEF n'a pas changé depuis le dernier envoi")
        );

        $this->add((new Text('tag'))
            ->setLabel("Tag éventuel (pour retrouver facilement un ensemble de logs) :")
        );

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
            'tag' => [
                'name' => 'tag',
                'required' => false,
            ],
        ];
    }
}