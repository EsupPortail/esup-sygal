<?php

namespace Formation\Form\EnqueteReponse;

use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\EnqueteReponse;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class EnqueteReponseForm extends Form
{
    use EntityManagerAwareTrait;

    public function init()
    {
        $questions = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->findAll();
        $questions = array_filter($questions, function (EnqueteQuestion $a) { return $a->estNonHistorise();});
        usort($questions, function(EnqueteQuestion $a, EnqueteQuestion $b) { return $a->getOrdre() > $b->getOrdre();});

        foreach ($questions as $question) {
            $this->add([
                'type' => Select::class,
                'name' => 'select_' . $question->getId(),
                'options' => [
                    'label' => "Niveau de satisfaction * :",
                    'empty_option' => "Choisisser un niveau de satisfaction",
                    'value_options' => EnqueteReponse::NIVEAUX,
                ],
                'attributes' => [
                    'id' => 'select_' . $question->getId(),
                    'class' => 'selectpicker',
                ],
            ]);
            $this->add([
                'type' => Textarea::class,
                'name' => 'textarea_' . $question->getId(),
                'options' => [
                    'label' => "Commentaire :",
                    'empty_option' => "Choisisser un niveau de satisfaction",
                    'value_options' => EnqueteReponse::NIVEAUX,
                ],
                'attributes' => [
                    'id' => 'textarea_' . $question->getId(),
                    'class' => 'tinymce',
                ],
            ]);
        }
        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);

        $filter = [];
        foreach ($questions as $question) {
            $filter['select_' . $question->getId() ] = ['required' => true,];
            $filter['textarea_' . $question->getId() ] = ['required' => false,];
        }
        $this->setInputFilter((new Factory())->createInputFilter( $filter ));


    }
}