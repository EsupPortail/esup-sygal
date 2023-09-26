<?php
namespace Admission\Fieldset\Inscription;

use Laminas\Form\Fieldset;

class InscriptionFieldset extends Fieldset
{
//    public function __construct($name = null, $options = array())
//    {
//        parent::__construct($name, $options);
//        $this->setLabel("Divers");
//
//        $this->add([
//            'name' => "infosEtudiant",
//            'type' => InformationsEtudiantFieldset::class,
//        ]);
//
//        $this->add([
//            'name' => "diplomeEtudiant",
//            'type' => NiveauEtudeFieldset::class,
//        ]);
//
//    }

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