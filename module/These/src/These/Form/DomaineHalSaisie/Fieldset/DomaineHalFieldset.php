<?php

namespace These\Form\DomaineHalSaisie\Fieldset;

use Laminas\Form\Fieldset;
use Laminas\Form\Element\Select;
use Laminas\InputFilter\InputFilterProviderInterface;

class DomaineHalFieldset extends Fieldset implements InputFilterProviderInterface
{
    private ?array $domainesHal = null;

    public function setDomainesHal($domainesHal): void
    {
        $this->domainesHal = $domainesHal;
        $this->get('domaineHal')->setValueOptions($this->domainesHal);
    }

    public function init()
    {
        $domaineHal = new Select('domaineHal', []);
        $domaineHal
            ->setLabel('Domaine(s) HAL')
            ->setDisableInArrayValidator(true)
            ->setAttributes([
                'class' => 'selectpicker show-tick form-control',
                'data-live-search' => 'true',
                'id' => 'domaineHal',
                'multiple' => 'true'
            ]);
        $this->add($domaineHal);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'domaineHal' => [
                'required' => false,
            ],
        ];
    }
}