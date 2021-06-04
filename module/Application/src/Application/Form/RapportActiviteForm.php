<?php

namespace Application\Form;

use Application\Entity\Db\Rapport;
use Application\Form\Rapport\RapportForm;
use Zend\Form\Element\Radio;

class RapportActiviteForm extends RapportForm
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'estFinal',
            'type' => Radio::class,
            'options' => [
                'label' => false,
                'value_options' => [
                    '0' => "Rapport d'activité annuel",
                    '1' => "Rapport d'activité de fin de thèse",
                ],
            ],
            'attributes' => [
                'id' => 'estFinal',
            ],
        ]);

        $this->bind(new Rapport());
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return array_merge(parent::getInputFilterSpecification(), [
            'estFinal' => [
                'required' => true,
            ],
        ]);
    }
}