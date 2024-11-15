<?php

namespace StepStar\Form\Generate;

use Application\Utils\FormUtils;
use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class GenerateForm extends Form implements InputFilterProviderInterface
{
    public function init(): void
    {
        parent::init();

        $this->add((new Text('these'))
            ->setLabel("Numéros de thèses (séparés par une virgule) :")
        );

        FormUtils::addSaveButton($this, "Générer");
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
        ];
    }
}