<?php
namespace Admission\Form\Fieldset;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;

class AdmissionBaseFieldset extends Fieldset
{
    public function disableModificationFieldset()
    {
        foreach ($this->getElements() as $element) {
            if ($element instanceof Checkbox || $element instanceof Select || $element instanceof File) {
                $element->setAttribute('disabled', true);
            } else {
                $element->setAttribute('readonly', true);
            }
        }
    }
}