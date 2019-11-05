<?php

namespace Application\Form;

use Application\Entity\Db\EcoleDoctorale;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\File;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\Validator\File\Extension;

class EcoleDoctoraleForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new EcoleDoctorale());

        $this
            ->add(new Hidden('id'));

        $this->add((
            new Text('sigle'))
                ->setLabel("Sigle :")
        );

        $this
            ->add((
                new Text('libelle'))
                    ->setLabel("Libellé :")
        );

        $this
            ->add((
                new Text('code'))
                    ->setLabel("Code :")
        );
        $this
            ->add((
                new File('cheminLogo'))
                ->setLabel('Logo de l\'école doctorale :')
            );
        $this
            ->add((
            new Submit('supprimer-logo'))
                ->setValue("Supprimer le logo")
                ->setAttribute('class', 'btn btn-danger')
            );

        $this
            ->add((
                new Submit('submit'))
                    ->setValue("Enregistrer")
                    ->setAttribute('class', 'btn btn-primary')
        );


        $this->setInputFilter((new Factory())->createInputFilter([
            'sigle' => [
                'name' => 'sigle',
                'required' => true,
            ],
            'libelle' => [
                'name' => 'libelle',
                'required' => true,
        ],
            'code' => [
                'name' => 'code',
                'required' => true,
            ],
            'cheminLogo' => [
                'required' => false,
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
        ]));
    }
}