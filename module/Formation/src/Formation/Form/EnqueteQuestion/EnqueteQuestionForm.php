<?php

namespace Formation\Form\EnqueteQuestion;

use Application\Utils\FormUtils;
use Formation\Service\EnqueteCategorie\EnqueteCategorieServiceAwareTrait;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class EnqueteQuestionForm extends Form {
    use EnqueteCategorieServiceAwareTrait;

    public function init()
    {
        //libelle
        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libellé de la question :",
            ],
            'attributes' => [
                'id' => 'libelle',
                'class' => 'required',
            ],
        ]);
        //description
        $this->add([
            'type' => Textarea::class,
            'name' => 'description',
            'options' => [
                'label' => "Complément d'information à propos de la question :",
            ],
            'attributes' => [
                'id' => 'description',
                'class' => 'tinymce',
            ],
        ]);
        //categorie
        $this->add([
           'type' => Select::class,
           'name' => 'categorie',
            'options' => [
                'label' => "Catégorie associée à la question :",
                'empty_option' => "Aucune catégorie",
                'value_options' => $this->getEnqueteCategorieService()->getCategoriesAsOptions(),
            ],
            'attributes' => [
                'id' => 'categorie',
                'class' => 'show-tick',
                'data-live-search' => 'true',
            ],
        ]);
        //ordre
        $this->add([
            'type' => Text::class,
            'name' => 'ordre',
            'options' => [
                'label' => "Ordre de la question <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'id' => 'ordre',
                'class' => 'required',
            ],
        ]);

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'libelle'       => [ 'required' => true, ],
            'description'   => [ 'required' => false, ],
            'ordre'         => [ 'required' => true, ],
        ]));
    }
}