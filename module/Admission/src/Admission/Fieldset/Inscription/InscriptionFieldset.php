<?php
namespace Admission\Fieldset\Inscription;

use Laminas\Form\Fieldset;

class InscriptionFieldset extends Fieldset
{
    public function init()
    {
        $this->add([
            'name' => "infosInscription",
            'type' => InformationsInscriptionFieldset::class,
        ]);

        $this->add([
            'name' => "specifitesEnvisagees",
            'type' => SpecifitesEnvisageesFieldset::class,
        ]);
    }
}