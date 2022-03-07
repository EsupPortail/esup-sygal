<?php

namespace Application\Form;

use Application\Entity\Db\Rapport;
use Application\Form\Rapport\RapportForm;
use Laminas\Form\Element\Radio;

class RapportActiviteForm extends RapportForm
{
    protected $estFinalValueOptions = [
        '0' => "Rapport d'activité annuel",
        '1' => "Rapport d'activité de fin de thèse",
    ];

    /**
     * @param string[] $estFinalValueOptions
     * @return self
     */
    public function setEstFinalValueOptions(array $estFinalValueOptions): self
    {
        $this->estFinalValueOptions = $estFinalValueOptions;
        return $this;
    }

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
                'value_options' => $this->estFinalValueOptions,
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
    public function prepare()
    {
        $this->get('estFinal')->setValueOptions($this->estFinalValueOptions);

        return parent::prepare();
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