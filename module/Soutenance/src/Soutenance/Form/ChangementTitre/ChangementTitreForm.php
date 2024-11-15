<?php

namespace Soutenance\Form\ChangementTitre;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;

class ChangementTitreForm extends Form {

    public function init()
    {
        $this->add(
            (new Textarea('titre'))
                ->setLabel("Nouveau titre :")
        );

        FormUtils::addSaveButton($this);
    }
}