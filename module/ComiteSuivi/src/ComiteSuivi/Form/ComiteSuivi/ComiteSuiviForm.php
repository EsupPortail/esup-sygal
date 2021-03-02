<?php

namespace ComiteSuivi\Form\ComiteSuivi;

use ComiteSuivi\Entity\DateTimeTrait;
use Zend\Form\Element\Button;
use Zend\Form\Element\DateTime;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class ComiteSuiviForm extends Form {
    use DateTimeTrait;

    public function init()
    {
        //these (hidden)
        $this->add([
            'name' => 'these',
            'type' => Hidden::class,
        ]);
        //année de thèse
        $this->add([
            'name' => 'annee_these',
            'type' => Select::class,
            'options' => [
                'label' => 'Année de thèse * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Choisissez une année de thèse ...",
                'value_options' => [
                    1  => "Première année",
                    2  => "Deuxième année",
                    3 => "Troisième année",
                    4 => "Quatrième année",
                    5 => "Cinquième année",
                    6 => "Sixème année",
                ],
            ],
            'attributes' => [
                'id' => 'annee_these',
            ],
        ]);
        //année scolaire
        $this->add([
            'name' => 'annee_scolaire',
            'type' => Select::class,
            'options' => [
                'label' => 'Année Scolaire * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Choisissez une année scolaire ...",
                'value_options' => $this->generateAnneesScolaires(),
            ],
            'attributes' => [
                'id' => 'annee_scolaire',
            ],
        ]);
        //date
        $this->add([
            'name' => 'date_comite',
            'type' => DateTime::class,
            'options' => [
                'label' => 'Date du comité * : ',
                'format' => 'd/m/Y',
            ],
            'attributes' => [
                'id' => 'date_comite',
            ],
        ]);
        //submit
        $this->add([
            'type' => Button::class,
            'name' => 'creer',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer' ,
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
            'annee_these' => [
                'required' => true,
            ],
            'annee_scolaire' => [
                'required' => true,
            ],
            'date_comite' => [
                'required' => true,
            ],
        ]));
    }

    /**
     * @param int $delta
     * @return array
     */
    private function generateAnneesScolaires($delta = 3) {
        /** @var integer $anneeCourante */
        $anneeCourante = $this->getDateTime()->format('Y');
        $array = [];
        for($annee = ($anneeCourante - $delta); $annee <= ($anneeCourante + $delta) ; $annee++) {
            $text = $annee . "/" . ($annee+1);
            $array[$text] = $text;
        }
        return $array;
    }
}