<?php

namespace ComiteSuivi\Form\CompteRendu;

use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Service\Membre\MembreServiceAwareTrait;
use Zend\Form\Element\Button;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class CompteRenduForm extends Form {
    use DateTimeTrait;
    use MembreServiceAwareTrait;

    public function init()
    {
        //these (hidden)
        $this->add([
            'name' => 'comite',
            'type' => Hidden::class,
        ]);
        //année de thèse
        $this->add([
            'name' => 'examinateur',
            'type' => Select::class,
            'options' => [
                'label' => 'Examinateur * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Choisissez un examinateur ...",
                'value_options' => [
                    3  => "Paule Hochon",
                ],
            ],
            'attributes' => [
                'id' => 'examinateur',
            ],
        ]);
        //année scolaire
        $this->add([
            'name' => 'reponse',
            'type' => Textarea::class,
            'options' => [
                'label' => 'Compte-Rendu * : ',
                'label_attributes' => [
                    'class' => 'control-label word',
                ],
            ],
            'attributes' => [
                'id' => 'reponse',
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
            'comite' => [
                'required' => true,
            ],
            'examinateur' => [
                'required' => true,
            ],
            'reponse' => [
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