<?php

namespace These\Fieldset\TitreAcces;

use Laminas\Form\Element\Date;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class TitreAccesFieldset extends Fieldset implements InputFilterProviderInterface
{
    private ?array $pays = null;

    public function setPays(array $paysAsOptions): void
    {
        $this->pays = $paysAsOptions;
        $this->get('codePaysTitreAcces')->setEmptyOption('Sélectionnez une option');
        $this->get('codePaysTitreAcces')->setValueOptions($this->pays);
    }


    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add(
            (new Select("titreAccesInterneExterne"))
                ->setEmptyOption("Sélectionnez une option")
                ->setValueOptions([
                    'E' => 'Externe',
                    'I' => 'Interne',
                ])
                ->setLabel("Accès : ")
        );

        $this->add(
            (new Text("libelleTitreAcces"))
                ->setLabel("Libellé : ")
        );

        $this->add(
            (new Select("codePaysTitreAcces"))
                ->setLabel("Pays d'obtention : ")
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "codePaysTitreAcces"
                ])
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'titreAccesInterneExterne' => [
                'name' => 'titreAccesInterneExterne',
                'required' => false,
            ],
            'libelleTitreAcces' => [
                'name' => 'libelleTitreAcces',
                'required' => false,
            ],
            'codePaysTitreAcces' => [
                'name' => 'codePaysTitreAcces',
                'required' => false,
            ],
        ];
    }
}