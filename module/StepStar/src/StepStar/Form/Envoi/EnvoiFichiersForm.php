<?php

namespace StepStar\Form\Envoi;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Validator\Callback;

class EnvoiFichiersForm extends AbstractEnvoiForm
{
    public function init(): void
    {
        parent::init();

        $this->add((new Text('path'))
            ->setLabel("Chemin absolu du répertoire contenant les fichiers TEF :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Envoyer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

    public function getInputFilterSpecification(): array
    {
        return array_merge(parent::getInputFilterSpecification(), [
            'path' => [
                'name' => 'path',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    ['name' => Callback::class, 'options' => [
                        'callback' => fn($path) => is_dir($path) && is_readable($path),
                        'messages' => [Callback::INVALID_VALUE => "Ce chemin ne pointe pas vers un répertoire lisible."],
                    ]],
                ],
            ],
        ]);
    }
}