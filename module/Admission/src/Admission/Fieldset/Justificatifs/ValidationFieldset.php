<?php
namespace Admission\Fieldset\Justificatifs;

use Admission\Fieldset\Justificatifs\PiecesJustificativesFieldset;
use Admission\Fieldset\Justificatifs\CircuitSignatureFieldset;

use Laminas\Form\Fieldset;

class ValidationFieldset extends Fieldset
{
    public function init()
    {
        $this->add([
            'name' => "pieces_justificatives",
            'type' => PiecesJustificativesFieldset::class,
        ]);

        $this->add([
            'name' => "circuit_signature",
            'type' => CircuitSignatureFieldset::class,
        ]);
    }
}