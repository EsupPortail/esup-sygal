<?php

namespace Structure\Form;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;
use Structure\Entity\Db\EcoleDoctorale;

/**
 * @property \Structure\Form\InputFilter\StructureInputFilterInterface $filter
 */
class EcoleDoctoraleForm extends StructureForm
{
    public function init(): void
    {
        parent::init();

        $this->setObject(new EcoleDoctorale());

        $this->add((new Text('sourceCode'))
            ->setLabel("Source Code")
        );

        $this->add((new Text('theme'))
            ->setLabel("Thème :")
        );

        $this->add((new Text('offre-these'))
            ->setLabel("Lien (URL) vers l'offre de thèse :")
        );

        $this->add((new Checkbox('estFerme'))
            ->setLabel("École doctorale fermée")
        );
    }

    public function prepare(): self
    {
        parent::prepare();

        $this->filter->prepareForm($this);

        return $this;
    }
}