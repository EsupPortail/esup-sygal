<?php

namespace These\Form\DomaineHalSaisie\Fieldset;

use Laminas\Form\Fieldset;
use Laminas\Form\Element\Select;

class DomaineHalFieldset extends Fieldset
{
    private ?array $domainesHal = null;

    public function setDomainesHal($domainesHal): void
    {
        $this->domainesHal = $domainesHal;
        $this->get('domaineHal')->setEmptyOption('Sélectionnez une option');
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
                'placeholder' => "Entrez les deux premières lettres...",
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
            'domaineHal' => ['required' => false],
        ];
    }
}