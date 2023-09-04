<?php

namespace Structure\Form;

use Laminas\Filter\ToNull;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

abstract class StructureForm extends Form implements InputFilterProviderInterface
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->add((new Text('sigle'))
            ->setLabel("Sigle :")
        );

        $this->add((new Text('libelle'))
            ->setLabel("Libellé :")
        );

        $this->add((new Text('code'))
            ->setLabel("Code :")
        );

        $this->add((new Text('id_ref'))
            ->setLabel("IdRef :")
        );

        $this->add((new Text('id_hal'))
            ->setLabel("IdHAL :")
        );

        $this->add((new File('cheminLogo'))
            ->setLabel('Logo :')
        );

        $this->add((new Submit('supprimer-logo'))
            ->setValue("Supprimer le logo")
            ->setAttribute('class', 'btn btn-danger')
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'sigle' => [
                'name' => 'sigle',
                'required' => false,
            ],
            'libelle' => [
                'name' => 'libelle',
                'required' => true,
            ],
            'code' => [
                'name' => 'code',
                'required' => false,
            ],
            'id_ref' => [
                'name' => 'id_ref',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
            ],
            'id_hal' => [
                'name' => 'id_hal',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
            ],
            'cheminLogo' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['bmp', 'png', 'jpg', 'jpeg'],
//                            'case' => false,
//                        ],
//                        'break_chain_on_failure' => true,
//                    ],
//                ],
            ],
        ];
    }
}