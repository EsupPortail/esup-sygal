<?php

namespace StepStar\Form\Envoi;

use Application\Utils\FormUtils;
use Laminas\Filter\StringTrim;
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

        FormUtils::addSaveButton($this, "Envoyer");
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