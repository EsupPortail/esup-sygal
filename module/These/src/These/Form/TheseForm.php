<?php

namespace These\Form;

use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use These\Fieldset\Generalites\GeneralitesFieldset;

class TheseForm extends Form implements InputFilterProviderInterface
{
    private GeneralitesFieldset $fieldsetGeneralites;

    /**
     * @param GeneralitesFieldset $fieldsetGeneralites
     * @return self
     */
    public function setFieldsetGeneralites(GeneralitesFieldset $fieldsetGeneralites): self
    {
        $this->fieldsetGeneralites = $fieldsetGeneralites;
        return $this;
    }

    public function init()
    {
        $this
            ->add($this->fieldsetGeneralites->setUseAsBaseFieldset(false))
            ->add((new Submit('submit'))->setValue('Enregistrer'));
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'titre' => [
                'name' => 'titre',
                'required' => true,
            ],
            'doctorant' => [
                'name' => 'doctorant',
                'required' => true,
            ],
            'discipline' => [
                'name' => 'discipline',
                'required' => false,
            ],
        ];
    }
}