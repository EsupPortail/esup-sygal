<?php

namespace Formation\Form\EnqueteReponse;

use Application\Utils\FormUtils;
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
                    'label' => "Niveau de satisfaction <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                    'label_options' => [ 'disable_html_escape' => true, ],
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

        FormUtils::addSaveButton($this);

        $this->add([
            'type' => Button::class,
            'name' => 'enregistrer_valider',
            'options' => [
                'label' => '<i class="fas fa-check"></i> Enregistrer et valider',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-success',
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