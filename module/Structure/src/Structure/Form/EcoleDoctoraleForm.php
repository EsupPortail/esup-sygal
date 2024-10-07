<?php

namespace Structure\Form;

use Laminas\Filter\ToNull;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Uri;
use Structure\Entity\Db\EcoleDoctorale;

class EcoleDoctoraleForm extends StructureForm implements InputFilterProviderInterface
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init(): void
    {
        parent::init();

        $this->setObject(new EcoleDoctorale());

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

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return array_merge(parent::getInputFilterSpecification(), [
            'code' => [
                'required' => true, // requis pour le calcul du nom de fichier logo
            ],
            'theme' => [
                'name' => 'theme',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
            ],
            'offre-these' => [
                'name' => 'offre-these',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
                'validators' => [
                    ['name' => Uri::class, 'options' => ['allowRelative' => false]],
                ],
            ],
            'estFerme' => [
                'name' => 'estFerme',
                'required' => false,
            ],
        ]);
    }
}