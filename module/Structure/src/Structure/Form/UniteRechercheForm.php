<?php

namespace Structure\Form;

use Laminas\Filter\ToNull;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\InputFilterProviderInterface;
use Structure\Entity\Db\UniteRecherche;

class UniteRechercheForm extends StructureForm implements InputFilterProviderInterface
{
    /**
     * NB: hydrateur injecté par la factory
     */
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

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return array_merge(parent::getInputFilterSpecification(), [
            'code' => [
                'required' => true, // requis pour le calcul du nom de fichier logo
            ],
            'RNSR' => [
                'name' => 'RNSR',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
            ],
        ]);
    }
}