<?php

namespace RapportActivite\Form;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Textarea;

/**
 * @property \RapportActivite\Entity\Db\RapportActivite $object
 */
class RapportActiviteAnnuelForm extends RapportActiviteAbstractForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'type' => Textarea::class,
            'name' => 'calendrierPrevionnelFinalisation',
            'options' => [
                'label' => "Calendrier prévisionnel de finalisation de la thèse (4-5 lignes max) / Provisional timetable to finalize the thesis (up to 4-5 lines) :",
                'label_attributes' => [
                    'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'rows' => 5,
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'preparationApresThese',
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

        if (!$this->object->getCalendrierPrevionnelFinalisationEnabled()) {
            $this->remove('calendrierPrevionnelFinalisation');
        }
        if (!$this->object->getPreparationApresTheseEnabled()) {
            $this->remove('preparationApresThese');
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return array_merge_recursive(parent::getInputFilterSpecification(), [
            'calendrierPrevionnelFinalisation' => [
                'required' => $this->object->getCalendrierPrevionnelFinalisationEnabled(),
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],
            'preparationApresThese' => [
                'required' => $this->object->getPreparationApresTheseEnabled(),
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],
        ]);
    }
}