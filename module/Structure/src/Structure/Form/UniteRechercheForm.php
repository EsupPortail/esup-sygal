<?php

namespace Structure\Form;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;
use Structure\Entity\Db\UniteRecherche;

/**
 * @property \Structure\Form\InputFilter\StructureInputFilterInterface $filter
 */
class UniteRechercheForm extends StructureForm
{
    public function init(): void
    {
        parent::init();

        $this->setObject(new UniteRecherche());

        $this->add((new Text('RNSR'))
            ->setLabel("Identifiant RNSR :")
        );

        $this->add((new Checkbox('estFerme'))
            ->setLabel("Unité de recherche fermée")
        );
    }

    public function prepare(): self
    {
        parent::prepare();

        $this->filter->prepareForm($this);

        return $this;
    }
}