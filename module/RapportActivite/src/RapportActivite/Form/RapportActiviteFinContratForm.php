<?php

namespace RapportActivite\Form;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Textarea;

class RapportActiviteFinContratForm extends RapportActiviteAbstractForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'type' => Textarea::class,
            'name' => 'perspectivesApresThese',
            'options' => [
                'label' => "Perspectives de carrière et démarches entreprises (7-8 lignes max) / Careers paths and actions undertaken (up to 7-8 lines) :",
                'label_attributes' => [
                    'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'rows' => 5,
            ],
        ]);
    }

    public function prepare()
    {
        parent::prepare();

        if (!$this->object->getPerspectivesApresTheseEnabled()) {
            $this->remove('perspectivesApresThese');
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return array_merge_recursive(parent::getInputFilterSpecification(), [
            'perspectivesApresThese' => [
                'required' => $this->object->getPerspectivesApresTheseEnabled(),
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],
        ]);
    }
}