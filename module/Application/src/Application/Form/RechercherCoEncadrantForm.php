<?php

namespace Application\Form;

use UnicaenApp\Form\Element\SearchAndSelect;
use Zend\Form\Element\Button;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class RechercherCoEncadrantForm extends Form {

    private $urlCoEncadrant;

    /**
     * @param string $urlCoEncadrant
     */
    public function setUrlCoEncadrant(string $urlCoEncadrant): void
    {
        $this->urlCoEncadrant = $urlCoEncadrant;
        $this->get('co-encadrant')->setAutocompleteSource($this->urlCoEncadrant);
    }

    public function init()
    {
        /**
         * SearchAndSelect sur les Individus de la structure fictives
         */
        $coEncadrant = new SearchAndSelect('co-encadrant', ['label' => "Co-encadrant * :"]);
        $coEncadrant
            ->setAutocompleteSource($this->urlCoEncadrant)
            ->setSelectionRequired(true)
            ->setAttributes([
                'id' => 'co-encadrant',
                'placeholder' => "Sélectionner un co-encadrant ... ",
            ]);
        $this->add($coEncadrant);

        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer / Save',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);

        $this->setInputFilter((new Factory())->createInputFilter([
            'co-encadrant' => [ 'required' => true ],
        ]));
    }
}