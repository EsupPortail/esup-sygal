<?php
namespace Admission\Fieldset\Etudiant;

use Laminas\Form\Fieldset;

class EtudiantFieldset extends Fieldset
{
    public function init()
    {
        $this->add([
            'name' => "infosEtudiant",
            'type' => InformationsEtudiantFieldset::class,
        ]);

        $this->add([
            'name' => "diplomeEtudiant",
            'type' => NiveauEtudeFieldset::class,
        ]);
    }
}