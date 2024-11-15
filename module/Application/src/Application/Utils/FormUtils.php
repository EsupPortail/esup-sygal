<?php
namespace Application\Utils;

use Laminas\Form\Element\Button;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

class FormUtils
{
    /**
     * Méthode statique pour ajouter un bouton "Enregistrer" à un formulaire.
     */
    public static function addSaveButton(Form|Fieldset $form, $label = 'Enregistrer')
    {
        $saveButton = new Button('submit');
        $saveButton->setLabel('<span class="icon icon-save"></span> '.$label)
            ->setLabelOptions([
                'disable_html_escape' => true,
            ])
            ->setAttributes([
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ]);

        $form->add($saveButton);
    }

    /**
     * Méthode statique pour ajouter un bouton "Enregistrer" à un formulaire.
     */
    public static function addUploadButton(Form $form, $label = 'Téléverser votre fichier')
    {
        $saveButton = new Button('submit');
        $saveButton->setLabel('<span class="icon icon-televerser"></span> '.$label)
            ->setLabelOptions([
                'disable_html_escape' => true,
            ])
            ->setAttributes([
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ]);

        $form->add($saveButton);
    }
}