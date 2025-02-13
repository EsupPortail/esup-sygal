<?php

namespace Acteur\Form;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;

class AbstractActeurForm extends Form
{
    protected string $acteurFieldsetClass;

    public function setActeurFieldsetClass(string $acteurFieldsetClass): void
    {
        $this->acteurFieldsetClass = $acteurFieldsetClass;
    }

    public function init(): void
    {
        /** @var \Acteur\Fieldset\AbstractActeurFieldset $acteurFieldset */
        $acteurFieldset = $this->getFormFactory()->getFormElementManager()->get($this->acteurFieldsetClass);
        $acteurFieldset->setUseAsBaseFieldset(true);
        $this->add($acteurFieldset);

        FormUtils::addSaveButton($this);

        $this->add(new Csrf('csrf'));
    }
}